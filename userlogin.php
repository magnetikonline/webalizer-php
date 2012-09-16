<?php
// userlogin.php



class userlogin {

	private $validlogin = FALSE;
	private $webalizerpath = '';



	public function execute() {

		if (isset($_POST['login'])) {
			// attempt to log in user
			foreach (unserialize(USERLOGINS) as $login) {
				list($username,$password,$webalizerpath) = $login;

				if (
					($this->getvalue('loginusername') == $username) &&
					($this->getvalue('loginpassword') == $password)
				) {
					// valid login found
					$this->validlogin = TRUE;

					// save webalizerpath for this login user
					$this->webalizerpath = rtrim($webalizerpath,'/') . '/';
					return;
				}
			}
		}

		// if not a successful login request present the initial login form
		$this->renderhtml();
	}

	public function getloggedin() {

		return $this->validlogin;
	}

	public function getwebalizerpath() {

		return $this->webalizerpath;
	}

	private function getvalue($inputname) {

		return (isset($_POST[$inputname])) ? trim($_POST[$inputname]) : '';
	}

	private function renderhtml() {

		echo(
			XHTMLDOCTYPE . XHTMLTAG .
			'<head>' .
				'<title>WebalizerPHP Login</title>' .
				'<link rel="stylesheet" type="text/css" href="style.css" />' .
				'<script type="text/javascript" src="userlogin.js"></script>' .
			'</head>' .
			'<body>' .
				'<form method="post" action="." id="login">' .
					'<p>' .
						'<label for="loginusername">Username</label>' .
						'<input type="text" size="20" name="loginusername" id="loginusername" />' .
					'</p>' .
					'<p>' .
						'<label for="loginpassword">Password</label>' .
						'<input type="password" size="20" name="loginpassword" id="loginpassword" />' .
					'</p>' .
					'<p>' .
						'<input type="submit" name="login" value="Login" />' .
					'</p>' .
				'</form>' .
			'</body>' .
			'</html>'
		);
	}
}
