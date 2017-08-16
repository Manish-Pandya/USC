<?php

/**
 * EmailGen short summary.
 *
 * EmailGen description.
 *
 * @version 1.0
 * @author intoxopox
 */

class EmailGen {

	/**
	 * Example corpus string
	 * @var mixed
	 */
	public $corpus = "This is a test story about {fish} and how they {toot} underwater.
		More specifically, how do {fish}'s {toot}s look, sound, and smell from the air.
		Would a low-flying {bird} be able to detect {fish}'s {toot}?
		Would {bird} be compelled to then {eat} {fish}
		Inquiring minds want to {know}";

	/**
	 * Summary of __construct
	 * @param mixed $corpus
	 */
	public function __construct($corpus) {
		if ($corpus != null) $this->corpus = $corpus;
	}

	/**
	 * Summary of swapFish
	 * @return string
	 */
	public function swapFish() {
		return "Flynn the really really fun Fish";
	}

	/**
	 * Macro key/value replacement map
	 * @return string[]
	 */
	public function macroMap() {
		return array(
			"{fish}"	=>	$this->swapFish(),
			"{toot}"	=>	"smelly fart",
			"{bird}"	=>	"Tweety Bird",
			"{eat}"		=>	"devour"
		);
	}

	/**
	 * Runs through the corpus string and replaces all string parts matching macroMap keys with their corrisponding value.
	 * @return mixed
	 */
	public function parse() {
		return str_replace(array_keys($this->macroMap()), array_values($this->macroMap()), $this->corpus);
	}

}