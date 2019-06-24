<?php
/**
 * Created by PhpStorm.
 * User: Дима
 * Date: 04.03.2019
 * Time: 18:16
 */
define('DB_HOST','127.0.0.1:3306');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'testparser');
$file1 = 'proba1.txt';
$file2 = 'proba2.txt';
$file3 = 'proba3.txt';
require_once "simple_html_dom.php";
require_once "db.class.php";
$url = "http://ananaska.com/vse-novosti/";
//Connection to DB
$db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if(isset ($argv[1])){
    $action = $argv[1];

} else{
    echo 'No action';
 //   exit;
}
if($action == 'catalog'){
    getArticlesLinksFromCatalog($url);
} elseif($action == 'articles'){

   while (true){
       echo $tmp_uniq = md5(uniqid().time());
       $null ='NULL';
       echo $sql = "UPDATE articles SET tmp_uniq = '{$tmp_uniq}' WHERE tmp_uniq IS NULL LIMIT 10";

       $db->query($sql);
        $articles = $db->query("SELECT url FROM articles WHERE tmp_uniq = '{$tmp_uniq}'");
        if(!$articles){
            echo "All done";
            exit;
        }
        foreach ($articles as $article){
       getArticleData($article[0]);

   }

    }

}
//Just get links to articles
function getArticleData($url){
    global $db;
    $file3 = 'proba3.txt';
    $article = file_get_html($url);

    $h1 = $article->find('h1', 0)->innertext; // выбрали заголовок
    $content = $article->find('article', 0)->plaintext;  //выбрали оттуда сам контент статьи

     $data = compact('h1', 'content');
   $time =  mktime();

    $sql = "update articles SET h1 = '{$h1}', content = '{$content}', date_parsed = '{$time}' where url = '{$url}'";
    $db->query($sql);
    return $data;


}
function getArticlesLinksFromCatalog($url){
    global $db;
    echo PHP_EOL.$url.PHP_EOL;
    $html= file_get_html($url);
    foreach ($html->find('a.read-more-link') as $link_to_article){
        $articles_url = $db->escape($link_to_article->href);
        $sql = "INSERT ignore INTO articles SET url = '{$articles_url}'";
        $db->query($sql);
        echo $link_to_article->href.PHP_EOL;
    }
    //Recursion to next page
    if($next_link = $html->find('a.next', 0)){
        getArticlesLinksFromCatalog($next_link->href);
    }
}
