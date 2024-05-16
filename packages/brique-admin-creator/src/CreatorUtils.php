<?php
namespace BriqueAdminCreator;

class CreatorUtils{

	public static function getCamelCase($string){
		return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
	}
	
	public static function getPascalCase($string){
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
	}

}