<?php

namespace FakePay\Twig;

class FakePayExtension extends \Twig_Extension
{
	public function getName() {
		return "fakepay";
	}

	public function getFilters() {
		return array(
			"md5"   => new \Twig_Filter_Method($this, "md5"),
			"sha1"  => new \Twig_Filter_Method($this, "sha1"),
		);
	}

	public function md5($input) {
		return md5($input);
	}

	public function sha1($input) {
		return sha1($input);
	}
}