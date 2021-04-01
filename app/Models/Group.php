<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'number',
        'confirmation_code'
    ];

    public function user()
    {
        $this->belongsTo(UserGroup::class, 'id', 'group_id');
    }
	
	public static function getGroupType($group_id) {
		//2- мужская, 1 - женская, 0 - смешенная
		$group_man = [
                198318499,
                198320103,
                198320123,
                198320155,
                198320168,
                198320191,
                198320202,
                198320213,
                198320223,
                198320457,
                198320488,
                198320507,
                198320520,
                198320542,
                198320558,
                198320570,
                198320588,
                198320609,
                198320625,
                198320640,
            ];
			
	$group_woman = [
                198385612,
                198385665,
                198385715,
                198385755,
                198385816,
                198385850,
                198385865,
                198385889,
                198385909,
                198385937,
                198385950,
                198385973,
                198385997,
                198386060,
                198386129,
                198386198,
                198386291,
                198386334,
                198386383,
                198386441,
            ];
			
	$group_mix = [
                198385937,
                198320588,
                198386441,
                198320558,
                198386383,
                198320609,
                198386060,
                198320507,
                198385909,
                198320488,
                198385937,
                198320457,
                198385889,
                198320223,
                198385865,
                198320213,
                198385755,
                198320155,
                198385715,
                198320123,
            ];
		
		if (in_array($group_id, $group_man)) {
			return 2;
		}
		elseif (in_array($group_id, $group_woman)) {
			return 1;
		}
		elseif (in_array($group_id, $group_woman)) {
			return 0;
		}
	}
}

