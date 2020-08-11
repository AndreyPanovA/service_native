<?php class Template {
	public $file; //template
	public $v = array();
	public $cycle_sep = array(); //cycle_name=>separator_string
	public $html;
	public $debug;
	public $is_remove_cycles = true;
	public $skip = array(); //insert '' for these markers (in keys!)
	
	public $tpl_string;
	
	private $caller;
	
/*
WARNING! If you want to use cycle template be sure that brackets {} are the only symbols in the line
IF
	{{marker}{true}{false}}
	no nesting, no cycles inside!
*/

	function __construct($file='',$tpl_string = false) {
		if ($tpl_string)
			$this->tpl_string = $this->file = $file;
		else
			$this->file = $file;
		if (!$this->tpl_string)
			$this->filename($this->file);
	}
	
	function filename($file) {
		if ($file) {
			$this->tpl_string = file_get_contents('tpl/'.$file.'.html',true);
		}
	}
	
	function getvars() { //reverse operation - gets all non condition markers from template
		$res = array();
		if (!$this->tpl_string)
			$this->filename($this->file);
		preg_match_all('/[^}]?{([\w\-\.\*]+)}[^{]?/ms',$this->tpl_string,$a);

		foreach ($a[1] as $t) {
			if (strpos($t,'.')) {
				$x = explode('.',$t);
				$res[$x[0]][$x[1]] = 1;
			} else {
				$res[$t] = 1;
			}
		}
		return $res;
	}
	
	function html() {
		if (!$this->tpl_string) {
			$cut = strlen(ROOT_DIR);
			$message = $this->file.NL;
			foreach (array_reverse(debug_backtrace()) as $i)
				$message .= substr($i['file'],$cut).' - '.$i['line'].NL;
			tf($message);
		}

		$simple = $if = $if_cycle = array();
		$this->html = $this->tpl_string;

		preg_match_all('/{{([\w\.\-\*]+)}{/ms',$this->html,$if); //to catch cycles and not replace it with simples
		
		foreach ($this->v as $k=>$v) {
			if ($this->skip[$k])
				$this->v[$k] = '';
			if (is_array($v[0])) {
				foreach (array_keys($v[0]) as $ck)
					$cycle[$k][] = '{'.$k.'.'.$ck.'}';
			} elseif (in_array($k,$if[1])) {
				$ifs[$k] = $this->v[$k];
			} else
				$simple['{'.$k.'}'] = $this->v[$k];
		}
		$this->html = str_replace(array_keys($simple),array_values($simple),$this->html);
		
		preg_match_all('/{{([\w\.\-\*]+)}{(.*?)}{(.*?)}}/ms',$this->html,$a);
		
		foreach ($a[0] as $index=>$if_struct) {
			if (strpos($a[1][$index],'.')) {
				$c = explode('.',$a[1][$index]);
				$if_cycle[$c[0]][$c[1]] = array($a[3][$index],$a[2][$index]);
				$if_replace = '{'.$a[1][$index].'}';
			} else {
				$if_replace = $a[$ifs[$a[1][$index]] ? 2 : 3][$index];
				//$if[$a[1][$index]] = array($a[3][$index],$a[2][$index]);
			}
			$this->html = str_replace($if_struct,$if_replace,$this->html);
		}

		// instead of \s there should be \v but some hostings don't recognize it
		preg_match_all('/[^{}]({\s([^{]*{\w+\.[\w\*]+}.*?)\s})[^{}]/ms',$this->html,$a);
		foreach ($a[2] as $i=>$test_cycle) { //test for real cycle, not { from js or something
			if (preg_match('/^[^{}]*[{}][^{}]*{\w+\.[\w\*]+}/ms',$test_cycle))
				unset($a[0][$i],$a[1][$i],$a[2][$i]);
		}

		if ($a[2]) {
			foreach ($a[2] as $i=>$tpl_cycle) {
				preg_match('/{(\w+)\.[\w\*]+}/',$tpl_cycle,$cycle_name);
				$cycle_done = array();
				$cycle_name = $cycle_name[1];
				if ($this->v[$cycle_name])
					foreach ($this->v[$cycle_name] as $set) {
						$itera = $tpl_cycle;
						$if_cycle_a = array();
						foreach ($set as $k=>$v) {
							$ckey = $cycle_name.'.'.$k;
							if ($if_cycle[$cycle_name][$k]) {
								$if_cycle_a[$k] = $v;
							} else {
								$itera = str_replace('{'.$ckey.'}',$v,$itera);
								if ($if_cycle[$cycle_name])
									foreach ($if_cycle[$cycle_name] as $ic0=>$ic1)
										$if_cycle[$cycle_name][$ic0] = str_replace('{'.$ckey.'}',$v,$ic1);
							}
						}
						
						if ($if_cycle[$cycle_name])
							foreach ($if_cycle[$cycle_name] as $ic0=>$ic1)
								$itera = str_replace('{'.$cycle_name.'.'.$ic0.'}',$ic1[$if_cycle_a[$ic0] ? 1 : 0],$itera);

						$cycle_done[] = $itera;
					}
				if ($this->is_remove_cycles) {
					$this->html = str_replace($a[1][$i],implode($this->cycle_sep[$cycle_name[1]],$cycle_done),$this->html);
                	$this->html = str_replace($tpl_cycle,'',$this->html);  //newx    remove unused sections
				}
            }
		}

		//remove unused cycles
		if ($this->is_remove_cycles) {
			foreach ($a[0] as $remove) {
				$this->html = str_replace($remove,'',$this->html);
			}
		}

		return trim($this->html);
	}
}