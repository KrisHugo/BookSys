<?php
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$categories = getCategories($conn);
if (!isset($_SESSION[$pageType]['query'])){
    initiateListQuerySessions($pageType);
}
/* 仅在发送了POST请求后才这么做 */
if (isset($_GET['reset'])) {
    initiateListQuerySessions($pageType);
} else if (isset($_GET['query'])) {
    $_SESSION[$pageType]['books']['query'] = (! empty($_POST['query']) ? $_POST['query'] : '');
    $_SESSION[$pageType]['books']['category'] = (! empty($_POST['category']) ? $_POST['category'] : '');
    $_SESSION[$pageType]['books']['stored'] = (! empty($_POST['stored']) ? $_POST['stored'] : '');
}
$query = $_SESSION[$pageType]['books']['query'];
$category = $_SESSION[$pageType]['books']['category'];
$stored = $_SESSION[$pageType]['books']['stored'];
?>