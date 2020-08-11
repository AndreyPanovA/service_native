<?php class Pageline {
	const marker = '{*}';
	
	public $size = 19; //number of displayed pagenumbers. 0 - display all
	public $total;
	private $current = 1;
	
	public $sign_left = '&lt;';
	public $sign_right = '&gt;';
	public $splitter;
	public $turning; //how much pages to turn when going left/right. By default equals to $size
	public $center = false; //if true current page will be at the center of pageline
	public $output_one = false; //if true pageline will be shown even there is only one page
	public $borders = false; //if true first and last pages always will be
	
	public $tpl_on; //required. <a href="news/{*}/show/" class="go on" id="some">-{*}-</a>
	public $tpl_off; //<a disabled class="go on" id="some">-{*}-</a> By default $tpl_on will be taken and link replaced
	
	function __construct($current=0,$num_elements=0,$onpage_elements=0) {
		if ($current) $this->current = $current;
		if ($num_elements && $onpage_elements) $this->total = ceil($num_elements / $onpage_elements);
	}
	
	function Output() {
		if ($this->total < 2 && !$this->output_one) return;
		if ($this->size) {
			if ($this->center) {
				$from = $this->current - ($this->size%2 ? floor($this->size/2) : floor($this->size/2) - 1);
				$to = $this->current + floor($this->size/2);
			} else {
				$from = floor($this->current / $this->size) * $this->size - 1;
				$to = $from + $this->size;
			}
			if ($from < 1) {
				$to -= $from-1;
				$from = 1;
			}
			if ($to > $this->total) {
				$from = max(1,$from-$to+$this->total);
				$to = $this->total;
			}
		} else {
			$from = 1;
			$to = $this->total;
		}

		if (!$this->tpl_off) $this->tpl_off = preg_replace('/(<a)[^>]*>([^\{]*\{\*\}[^\1]*)<\/a>/','$2',$this->tpl_on);
		
		if (!$this->turning) $this->turning = $this->center ? ceil($this->size/2) : $this->size;
		
		$res = '';
		
		if ($this->borders && $from > 1)
			$res .= str_replace(Pageline::marker,1,$this->tpl_on).$this->splitter;
		
		if ($this->sign_left && (($step = $from-1) > 0) && (!$this->borders || $this->borders && $from > 2)) {
			//if (($step = $this->current-$this->turning) > 0) 
			$sign_left = preg_replace('/((<a)[^>]*>)([^\{])*\{\*\}([^\2]*)(<\/a>)/','$1${3}'.$this->sign_left.'$4$5',$this->tpl_on);
			$sign_left = str_replace(Pageline::marker,$step,$sign_left);
			$res .= $sign_left.$this->splitter;
		}
		
		for ($i=$from;$i<=$to;$i++)
			$res .= ($i!=$from ? $this->splitter : '').str_replace(Pageline::marker,$i,$this->current==$i ? $this->tpl_off : $this->tpl_on);
		
		if ($this->sign_right && $this->total > $to + ($this->borders ? 1 : 0))/*
			if ($this->current==$this->total)
				$res .= '';//str_replace(Pageline::marker,$this->sign_right,$this->tpl_off);
			elseif ($this->total > $to) */{
				//($step = $this->current+$this->turning) <= $this->total)
				$step = $to+1;
				$sign_right = preg_replace('/((<a)[^>]*>)([^\{])*\{\*\}([^\2]*)(<\/a>)/','$1${3}'.$this->sign_right.'$4$5',$this->tpl_on);
				$sign_right = str_replace(Pageline::marker,$step,$sign_right);
				$res .= $this->splitter.$sign_right;
			}

		if ($this->borders && $to < $this->total)
			$res .= $this->splitter.str_replace(Pageline::marker,$this->total,$this->tpl_on);

		return $res;		
	}
}