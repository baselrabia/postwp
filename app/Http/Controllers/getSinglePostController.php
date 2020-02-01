<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class getSinglePostController extends Controller
{

    public $wp_site='http://8gharb.com';
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke($page= 1,$per_page= 20,Request $request)
    {

        $string = file_get_contents("$this->wp_site/wp-json/wp/v2/posts/?page=$page&per_page=$per_page");
        $posts = json_decode($string, true);


        for ($postId=0; $postId < $per_page; $postId++) {
            # code...

        $title= $posts[$postId]['title']['rendered'];
        $titleWithPdf=str_replace("&#8211;", "pdf", $title);
        $title= 'تحميل ' . $titleWithPdf;

        $content= $posts[$postId]['content']['rendered'];
        $category= $this->getCategory($posts[$postId]['categories'][0]);

        $pos = strpos($title, "pdf");
        $pos2 = strpos($titleWithPdf, "pdf");
        $tags =[
            substr($title, 0, $pos + 3),
            substr($title, $pos + 3),
            $x= substr($titleWithPdf, 0, $pos2 + 3),
            'تنزيل ' . $x,
        ];


        $returnContent= $this->returnContent($content);

        $returnText = $tags[3] . $tags[1] ." ". strip_tags($returnContent[0]);
        $contentImage= $returnContent["img"];
        $contentLink = $returnContent["link"];




        $newposts[]= [
            'title'=>$title,
            'content'=>$returnText,
            'image'=>$contentImage,
            'link'=>$contentLink,
            'category'=>$category,
            'tags'=>$tags,
        ];
/*
        foreach ($post as $key) {
            if (is_array($key)){
                foreach ($key as $tag) {
                    echo $tag . "</br>";
                }
            }else {
                echo $key. "</br></br>";
            }
        };
*/
}

        dd($newposts) ;


        // https://8gharb.com//wp-json/wp/v2/categories/3348 for get catageory
        //$category = $this->getCategory('3348');

        // https://8gharb.com//wp-json/wp/v2/tags/33947 for get tags

    }


    public function getCategory($id){
        $string = file_get_contents("$this->wp_site/wp-json/wp/v2/categories/$id");
        $category = json_decode($string, true);
        return $category['name'];

    }


    public function returnContent($content)
    {

        $dom = new \DOMDocument();
        $dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $content);

        $new["link"] = null;

        foreach ($dom->getElementsByTagName('p') as $p) {
            $new[]= $dom->saveHTML($p);

                if ($new["link"] == null) {
                    foreach ($dom->getElementsByTagName('a') as $a) {
                           if (strpos($a->getattribute('href') , 'pdf') !== false  ){
                            $new["link"] = $a->getattribute('href');
                        }
                    }
                }
        }

        foreach ($dom->getElementsByTagName('img') as $img) {
            $new["img"] = $img->getattribute('src');
        }

        return $new; //array of text & img

    }



}
