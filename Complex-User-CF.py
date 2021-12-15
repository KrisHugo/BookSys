#!/usr/bin/env python
#-*- coding: utf-8 -*-
import sys
sys.path.append('F:\softwares\Anaconda3\Scripts')
import mysql.connector,pymysql
#导入必要的数据包
import numpy as np
import pandas as pd
from math import *

def getUserInfo():
    # 抽取用户信息
    readerSql = "SELECT id, name, sex, college FROM reader WHERE college IS NOT NULL"
    readerList = getResultsFromSql(readerSql)
    readerList
    readerDic = {}
    for reader in readerList:
        readerDic[reader[0]] = reader
    return readerDic

def getResultsFromSql(sql):
    # 打开数据库连接
    db = pymysql.connect(host="localhost",user="root",password="root3306",database="booksys",charset="utf8")
    # 使用 cursor() 方法创建一个游标对象 cursor
    cursor = db.cursor()
    # 执行sql语句
    cursor.execute(sql)
    results = cursor.fetchall()
    
    db.close()
    return results

def getUserScores():
    userScores = {}
    ratingResults = getResultsFromSql("SELECT * FROM book_rate")
    borrowSql = """
    SELECT A.userId, A.bookId, SUM(A.totalBorrowDays) as totalBorrowDays, SUM(A.borrowTimes) as totalBorrowTimes FROM
     (SELECT userId, bookId, SUM(returnDate - borrowDate + 1) as totalBorrowDays, COUNT(1) as borrowTimes FROM borrow WHERE `status` = 'normal' and `returnDate` IS NOT NULL GROUP BY userId, bookId 
    UNION ALL 
    SELECT userId, bookId, SUM(CURDATE() - borrowDate + 1) as totalBorrowDays, COUNT(1) as borrowTimes FROM borrow WHERE `status` = 'normal' and `returnDate` IS NULL GROUP BY userId, bookId) as A GROUP BY A.userId, A.bookId;
    """
    borrowResults = getResultsFromSql(borrowSql)
    
    userList = getResultsFromSql("SELECT id FROM reader")
    
    #以rating计算
    ratingData = pd.DataFrame(ratingResults)[[1,2,3]]
    ratingData.columns =['bookId','userId','rating']
    ratingData = ratingData.sort_values('userId')
    for rating in ratingData.itertuples():
        #如果字典中没有某位用户，则使用用户ID来创建这位用户
        if not int(rating[2]) in userScores.keys():
            userScores[rating[2]] = {rating[1] : rating[3]}
        else:
            userScores[rating[2]][rating[1]] = rating[3]
    #以借阅记录计算
    borrowData = pd.DataFrame(borrowResults)
    borrowData.columns = ['userId', 'bookId', 'days', 'times']
    borrowData = borrowData.sort_values('userId')
    for borrow in borrowData.itertuples():
        score = int(borrow[3]) * int(borrow[4]) / 100
        #如果字典中没有某位用户，则使用用户ID来创建这位用户
        if not int(borrow[1]) in userScores.keys():
            userScores[borrow[1]] = {borrow[2]: score}
        elif not int(borrow[2]) in userScores[borrow[1]].keys():
            userScores[borrow[1]][borrow[2]] = score
        else:
            userScores[borrow[1]][borrow[2]] += score
    return userScores

#获取用户信息上的相似度
def infoCompare(info, user1, user2):
    similarity = 0
    if user1 in info.keys():
        if user2 in info.keys():
            info1 = info[user1]
            info2 = info[user2]
            if info1[2] == info2[2]:
                similarity += 2
            if info1[3] == info2[3]:
                similarity += 2
    return similarity

#获取具有一定相似信息的用户
def getInfoSimilarity(info, userId):
    similarDic = {}
    for neighborId in info.keys():
        #排除与自己计算相似度
        if not neighborId == userId:
            similar = infoCompare(info, userId, neighborId)
            if similar != 0: # 屏蔽完全不相干的用户
                similarDic[neighborId] = similar
    return similarDic

#欧氏距离计算用户相似度
def RatingEuclidean(scoreData, user1, user2, infoSimilarity):
    #取出两位用户的图书分值
    if user1 in scoreData.keys():
        score1 = scoreData[user1]
        score2 = scoreData[user2]
        distance = 0
        #找到两位用户都评论过的图书，并通过评分计算欧式距离 
        for key in score1.keys():
            if key in score2.keys():
                #注意，distance越大表示两者越相似
                distance += pow(float(score1[key]) - float(score2[key]), 2)
        return 1/(1 + sqrt(distance) + infoSimilarity)#这里返回值越小，相似度越大
    else:
        return 1
    
# 计算某个用户与其他用户的相似度
def top10_similar(data, info, userId):
    res = []
    infoSimilarDic = getInfoSimilarity(info, userId)
    for neighborId in data.keys():
        #排除与自己计算相似度
        if not neighborId == userId:
            if neighborId in infoSimilarDic.keys():
                infoSimilarity = infoSimilarDic[neighborId]
            else:
                infoSimilarity = 0 
            similar = RatingEuclidean(data, userId, neighborId, infoSimilarity)
            if similar < 1: # 屏蔽完全不相干的用户
                res.append((neighborId, similar))
    res.sort(key=lambda val:val[1])
    return res[:20]


def complex_recommend(user):
    scoreData = getUserScores()
    info = getUserInfo()
    recommendations = []
    if user in scoreData.keys():
        for sim_user in top10_similar(scoreData, info, user):
            #相似度最高的用户的图书记录
            items = scoreData[sim_user[0]]
            #筛选出该用户未借阅的图书并添加到列表中
            for item in items.keys():
                if item not in scoreData[user].keys():
                    recommendations.append((item,items[item]))
        recommendations.sort(key=lambda val:val[1],reverse=True)#按照评分排序
    #返回评分最高的10本图书
    return recommendations[:10]

def getRecBookFromDic(Recommendations):
    books = []
    for book in Recommendations:
        books.append(str(book[0]))
    return books
print(getRecBookFromDic(complex_recommend(int(sys.argv[1]))))
# print(sys.argv[1])
