<?php
/*
Plugin Name:    Context Post Segmenter
Description:    Wrap H3 segments in div for presentation.
Text-Domain:    cxt-post-segmenter
Version:        1.0
Author:         Robert Andrews
Author URI:     http://www.robertandrews.co.uk
License:        GPL v2 or later
License URI:    https://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * This plugin finds H3 content "segments" in posts and wraps them in
 * both Bootstrap cards and <section> elements.
 * These segments are <h3> to the next <h3> or, else, to post end.
 * 
 * In this way, we can impactful cards from plain article areas denoted
 * by <h3> crossheads and text beneath.
 * 
 * The plugin does one additional thing. Where an image is found
 * immediately following a <h3> element, we move it above the heading,
 * where it is transformed into a Bootstrap card image by means of
 * applying .card-img-top.
 * 
 * Some underlying code contributed by @jack, found at https://stackoverflow.com/a/10683463/1375163
 */

add_filter( 'the_content', 'segment_post', 50 );
function segment_post( $content ) {

    $content = utf8_decode($content); // https://stackoverflow.com/questions/1269485/how-do-i-tell-domdocument-load-what-encoding-i-want-it-to-use

    // Load post as document object module
    $dom = new DOMDocument('1.0', 'iso-8859-1');
    libxml_use_internal_errors(true);
    $dom->loadHTML($content);
    libxml_clear_errors();

    // Initialise variables
    $segments = array();
    $card_body = null;
    $section_position=1;

    // For each H3 element
    foreach ($dom->getElementsByTagName('h3') as $h3) {

        $found_image = null;

        // 1. First, collect all nodes
        $nodes = array($h3);

        // Remove image after H3
        if ($h3->nextSibling->nextSibling->nodeName == 'img') {
            $found_image = $h3->nextSibling->nextSibling;
            $h3->parentNode->removeChild($found_image);
        }

        // 2. Generate div.card-body
        // Iterate until another h3 or no more siblings
        for ($next = $h3->nextSibling; $next && $next->nodeName != 'h3'; $next = $next->nextSibling) {
            $nodes[] = $next;
        }
        $card_body = $dom->createElement('div');
        $card_body->setAttribute('class', 'card-body p-4');
        // Wrap the h3 segment...
        // i. Replace the h3 segment with the new card_body
        $h3->parentNode->replaceChild($card_body, $h3);
        // iii. And add all nodes back into the newly created card_body
        foreach ($nodes as $node) {
            $card_body->appendChild($node);
        }
        // keep title of the original h3
        // $segments[] = $h3->nodeValue;


        // 3. Wrap everything in <section> with .card class
        // custom function in /wp-content/plugins/cxt-bs-transformer/cxt-bs-transformer.php
        //Create new wrapper div
        $new_div = $dom->createElement('section');
        $new_div->setAttribute('class','card mb-3');
        $new_div->setAttribute('id','section-'.$section_position);
        $h3->setAttribute('data-anchor-id','section-'.$section_position); // data-anchor-id attribute helps AnchorJS identify a different 'id', https://www.bryanbraun.com/anchorjs/#section-ids

        //Clone our created div
        $new_div_clone = $new_div->cloneNode();
        //Replace image with this card section
        $card_body->parentNode->replaceChild($new_div_clone,$card_body);
        
        // add in found image
        if (!empty($found_image)) {
            // Add back here
            $found_image->setAttribute('class', 'card-img-top');
            $new_div_clone->appendChild($found_image);
        }
        
        //Append the contents to card section
        $new_div_clone->appendChild($card_body);


        // TODO: This seems superfluous
        /*
        // 3. Also wrap with <section>
        // Initialise the new card_body
        $card_body = $dom->createElement('section');
        //Clone our created element
        $card_body_clone = $card_body->cloneNode();
        //Replace image with this card_body div
        $card_body->parentNode->replaceChild($card_body_clone,$card_body);
        //Append the element to card_body div
        $card_body_clone->appendChild($card_body);
        */

        $section_position++;
    }

    // TODO: Ready for creating a table of contents
    // Make a Table of Contents
    // make sure we have segments (card is the last inserted card in the dom)
    /*
    if ($segments && $card_body) {
        $ul = $dom->createElement('ul');
        foreach ($segments as $title) {
            $li = $dom->createElement('li');

            $a = $dom->createElement('a', $title);
            $a->setAttribute('href', '#');

            $li->appendChild($a);
            $ul->appendChild($li);
        }

        // add as sibling of last card added
        $card_body->parentNode->appendChild($ul);
    }
    */

    // TODO: examine https://stackoverflow.com/questions/10703057/wrap-all-html-tags-between-h3-tag-sets-with-domdocument-in-php

    $content = $dom->saveHTML();
    return $content;
}
?>