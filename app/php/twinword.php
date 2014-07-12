<?php

require_once '../php/unirest-php/lib/Unirest.php';


// These code snippets use an open-source library. http://unirest.io/php
$response = Unirest::get("https://twinword-category-recommendation.p.mashape.com/recommend/?entry=cat&threshold=2.0",
  array(
    "X-Mashape-Key" => "qipqWiEdDbmshdzxhHHfxK9TvTDOp1ukzGtjsnbb92WAE28JCa"
  )
);

print_r($response);

?>