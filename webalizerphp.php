<?php
// webalizerphp.php



class webalizerphp {

	const loginkey = 'loggedin';

	private $loggedin = FALSE;
	private $webalizerpath = '';



	public function __construct() {

		session_start();

		if (isset($_SESSION[self::loginkey])) {
			$this->loggedin = TRUE;
			$this->webalizerpath = $_SESSION[self::loginkey];
		}
	}

	public function renderstats() {

		if (isset($_POST['logout'])) {
			// logout user
			unset($_SESSION[self::loginkey]);
			$this->redirecttohome();

			return;
		}

		if (!$this->loggedin) {
			// present login screen
			$userlogin = new userlogin();
			$userlogin->execute();

			if ($userlogin->getloggedin()) {
				// user has logged in
				$this->loggedin = TRUE;
				$this->webalizerpath = $userlogin->getwebalizerpath();

				// save webalizer path to session variable
				$_SESSION[self::loginkey] = $userlogin->getwebalizerpath();

				// redirect back to the start to avoid "post content's again?" browser prompts when jumping back/forth between webalizer pages
				$this->redirecttohome();
				return;
			}

		} else {
			// render stats page
			$renderstatpage = new renderstatpage($this->webalizerpath);

			if (isset($_GET['m'])) {
				$renderstatpage->setmonth($_GET['m']);
			}

			$renderstatpage->execute();
		}
	}

	public function displaypng() {

		if (
			(!$this->loggedin) ||
			(!isset($_GET['i'])) ||
			(!is_file($this->webalizerpath . $_GET['i'] . '.png'))
		) {
			return;
		}

		$filename = $this->webalizerpath . $_GET['i'] . '.png';

		// send content headers
		header('Content-Type: image/png');
		header('Content-Length: ' . filesize($filename));

		// dump the picture
		readfile($filename);
	}

	private function redirecttohome() {

		header('Location: http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']),'/') . '/');
	}
}
