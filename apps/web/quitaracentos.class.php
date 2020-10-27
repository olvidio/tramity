<?php
namespace web;

class QuitarAcentos {
	
	public static function convert($source) {
		
		// dirty solution to stop mb_convert_encoding from filling your string 
		// with question marks whenever it encounters an illegal character for the target encoding. 

		// detect the character encoding of the incoming file
		$encoding = mb_detect_encoding( $source, "auto" );
		// escape all of the question marks so we can remove artifacts from
		// the unicode conversion process
		$target = str_replace( "?", "[question_mark]", $source );

		$target = mb_convert_encoding( $target, 'UTF-8', $encoding);
		// remove any question marks that have been introduced because of illegal characters
		$target = str_replace( "?", "", $target );
		// replace the token string "[question_mark]" with the symbol "?"
		$target = str_replace( "[question_mark]", "?", $target );
		
		$target = preg_replace("/á|à|â|ã|ª/","a",$target);
		$target = preg_replace("/Á|À|Â|Ã/","A",$target);
		$target = preg_replace("/é|è|ê/","e",$target);
		$target = preg_replace("/É|È|Ê/","E",$target);
		$target = preg_replace("/í|ì|î/","i",$target);
		$target = preg_replace("/Í|Ì|Î/","I",$target);
		$target = preg_replace("/ó|ò|ô|õ|º/","o",$target);
		$target = preg_replace("/Ó|Ò|Ô|Õ/","O",$target);
		$target = preg_replace("/ú|ù|û/","u",$target);
		$target = preg_replace("/Ú|Ù|Û/","U",$target);
		$target = str_replace("/","_",$target);
		$target = str_replace(".","_",$target);
		$target = str_replace(" ","_",$target);
		$target = str_replace("ñ","n",$target);
		$target = str_replace("Ñ","N",$target);
		
		$target = preg_replace('/[^a-zA-Z0-9_.-]/', '', $target);
		return $target;
	}
}