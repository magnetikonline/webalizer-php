<?php
// renderstatpage.php



class renderstatpage {

	private $webalizerpath = '';
	private $statmonth = NULL;



	public function __construct($inputpath) {

		$this->webalizerpath = $inputpath;
	}

	public function setmonth($inputmonth) {

		$this->statmonth = $inputmonth;
	}

	public function execute() {

		// check for valid webalizer path
		if (!is_file($this->webalizerpath . 'index.html')) {
			// invalid webalizer path
			die('Error: Invalid webalizer path');
		}

		if (
			(!is_null($this->statmonth)) &&
			(!is_file($this->webalizerpath . 'usage_' . $this->statmonth . '.html'))
		) {
			// invalid statmonth, reset back to the stat index
			$this->statmonth = NULL;
		}

		if (is_null($this->statmonth)) {
			$statpagesource = $this->transformsource(
				file_get_contents($this->webalizerpath . 'index.html')
			);

		} else {
			$statpagesource = $this->transformsource(
				file_get_contents($this->webalizerpath . 'usage_' . $this->statmonth . '.html'),
				FALSE
			);
		}

		echo($statpagesource);
	}

	private function transformsource($inputsource,$inputindexpage = TRUE) {

		$statsource = $inputsource;

		// update doc type
		$statsource = str_replace(
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">',
			XHTMLDOCTYPE,
			$statsource
		);

		// update opening <html> tag
		$statsource = str_replace('<HTML>',XHTMLTAG,$statsource);
		$statsource = preg_replace('/<HTML lang="[^"]+">/',XHTMLTAG,$statsource);

		// add character encoding type, page stylesheet and javascript (if on monthly detail page)
		$statsource = str_replace(
			'</TITLE>',
			'</title>' .
			'<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />' .
			'<link rel="stylesheet" type="text/css" href="style.css" />' .
			((!$inputindexpage) ? '<script type="text/javascript" src="jumpsectionbox.js"></script>' : ''),
			$statsource
		);

		// fixup body tag and add logout button to header of page
		$statsource = str_replace(
			'<BODY BGCOLOR="#E8E8E8" TEXT="#000000" LINK="#0000FF" VLINK="#FF0000">',
			'<body>' .
			'<form method="post" action="." id="logout">' .
				'<p>' .
					'<input type="submit" name="logout" value="Logout" />' .
				'</p>' .
			'</form>',
			$statsource
		);

		// fixup index page footer totals row in table
		if ($inputindexpage) {
			$statsource = str_replace(
				'<TH BGCOLOR="#C0C0C0" COLSPAN=6 ALIGN=left><FONT SIZE="-1">Totals</FONT></TH>',
				'<th colspan="6" class="totals">Totals</th>',
				$statsource
			);
		}

		// a chunk of generic HTML => XHTML code cleanups
		$statsource = $this->bulkstrreplace($statsource,array(
			'<TR><TH HEIGHT=4></TH></TR>','',
			' NOWRAP',' class="name"',
			'HREF=','href=',
			'<P>','',
			'<HR>','',
			'</FONT>','',
			' BGCOLOR="#C0C0C0"','',
			'<BR>','<br />',
			'<CENTER>','<div id="content">',
			'</CENTER>','</div>',
			'SRC=','src=',
			'ALT=','alt=',
			'<SMALL><STRONG>','<p>',
			'</STRONG></SMALL>','</p>'
		));

		// some more generic HTML => XHTML code cleanups for stat month pages
		if (!$inputindexpage) {
			$statsource = $this->bulkstrreplace($statsource,array(
				' ALIGN=CENTER','',
				'<FONT SIZE=-1 COLOR="#C0C0C0">.<','&nbsp;<',

				// column widths
				' WIDTH=380>','>',
				' WIDTH=65>','>',
				' WIDTH=65 ',' ',

				// jump to links converted to an unordered list - part a
				"<SMALL>\n<A href=\"",'<ul id="jumpsection"><A href="',
				"</A>\n</SMALL>\n\n<TABLE",'</A></UL><TABLE'
			));
		}

		// footer message fixup
		$statsource = $this->bulkstrreplace($statsource,array(
			"<TABLE WIDTH=\"100%\" CELLPADDING=0 CELLSPACING=0 BORDER=0>\n<TR>\n<TD ALIGN=left VALIGN=top>\n<SMALL>",'<p>',
			"</SMALL>\n</TD>\n</TR>\n</TABLE>",'</p><p>Served up and secured by <a href="http://magnetikonline.com/webalizerphp/">WebalizerPHP Version 0.28</a></p>',
			'<STRONG>','',
			'</STRONG>',''
		));

		// header column colours
		// handling both version 2.20-01 and 2.01-10+ colors
		$statsource = $this->bulkstrreplace($statsource,array(
			' BGCOLOR="#008040"',' class="green"',
			' BGCOLOR="#00805c"',' class="green"',
			' BGCOLOR="#0080FF"',' class="blue"',
			' BGCOLOR="#0040ff"',' class="blue"',
			' BGCOLOR="#00E0FF"',' class="lgtblue"',
			' BGCOLOR="#00e0ff"',' class="lgtblue"',
			' BGCOLOR="#FFFF00"',' class="yellow"',
			' BGCOLOR="#ffff00"',' class="yellow"',
			' BGCOLOR="#FF8000"',' class="orange"',
			' BGCOLOR="#ff8000"',' class="orange"',
			' BGCOLOR="#FF0000"',' class="red"',
			' BGCOLOR="#ff0000"',' class="red"'
		));

		if (!$inputindexpage) {
			// daily statistics weekend row colour
			$statsource = str_replace(' BGCOLOR="#D0D0E0"',' class="weekend"',$statsource);
		}

		// remove/clean up other random tag junk
		$statsource = preg_replace('/<FONT SIZE="?-[1-2]"?>/','',$statsource);
		$statsource = preg_replace('/<IMG([^>]+)>/','<img$1 />',$statsource);
		$statsource = preg_replace('/<TABLE[^>]+>/','<table>',$statsource);
		$statsource = preg_replace('/ ALIGN=(center|left|right)/','',$statsource);
		$statsource = preg_replace('/ (COLSPAN|ROWSPAN|HEIGHT|WIDTH)=([0-9]+)/e',"' ' . strtolower('$1') . '=\"$2\"'",$statsource);

		if ($inputindexpage) {
			// correctly close out some bad table rows on the index usage page
			// - yearly total fixups (added in version 2.21)
			// - remove an un-required background color used on <th> cells
			// - right align yearly total cells with class="yeartotal", rather than align attribute
			$statsource = preg_replace('/<\/TH>\n?<TR>/','</th></tr><tr>',$statsource);
			$statsource = str_replace('" BGCOLOR="#D0D0E0"','"',$statsource);
			$statsource = str_replace('TH ALIGN="right"','th class="yeartotal"',$statsource);
		}

		if (!$inputindexpage) {
			// KB/MB/GB transfer stats generated by Geolizer patch
			$statsource = preg_replace('/><B>([0-9\.]+)&nbsp;([KBMBGB]+)<\/B></',' class="transfer">$1 $2<',$statsource);

			$statsource = preg_replace('/><B>([0-9]+)<\/B></',' class="num">$1<',$statsource);
			$statsource = preg_replace('/A NAME="([^"]+)"/e',"'a id=\"' . strtolower('$1') . '\"'",$statsource);

			// flag icons
			$statsource = preg_replace(
				'/<img src=\'flags\/([a-z]{2})\.png\' width="18" height="12" \/>/',
				'<img src="flags/$1.png" width="18" height="12" alt="" />',
				$statsource
			);

			// jump to links converted to an unordered list - part b
			$statsource = preg_replace(
				'/<A href="#([^"]+)">\[([^<]+)\]<\/A>/e',
				"'<li><a href=\"#' . strtolower('$1') . '\">$2</a></li>'",
				$statsource
			);
		}

		if (!$inputindexpage) {
			$statsource = str_replace('<TR><TD>','<tr><td class="stattype">',$statsource);
			$statsource = preg_replace('/<TR([^>]*)>\n?<TD class="num">/','<tr$1><td class="pos">',$statsource);
		}

		// convert all <h2> tags to <h1> tags
		$statsource = preg_replace('/<(\/)?H2>/','<$1h1>',$statsource);

		// lower case all other tags
		$statsource = preg_replace('/<(\/)?([A-Z]+)/e',"'<$1' . strtolower('$2')",$statsource);

		if ($inputindexpage) {
			// rebase urls to usage pages
			$statsource = preg_replace('/"usage_([0-9]+)\.html"/','"$1"',$statsource);
		}

		return $statsource;
	}

	// bulkstrreplace() just performs a str_replace passing arrays for search/replace
	// but with a refactored parameter list to aid readability of source code when called
	private function bulkstrreplace($inputtext,array $inputsearchreplacelist) {

		$searchlist = array();
		$replacelist = array();
		$searchreplacelist = $inputsearchreplacelist;

		while ($searchreplacelist) {
			$searchlist[] = array_shift($searchreplacelist);
			$replacelist[] = array_shift($searchreplacelist);
		}

		return str_replace($searchlist,$replacelist,$inputtext);
	}
}
