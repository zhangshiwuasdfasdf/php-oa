<?php

class PositionSequenceModel extends CommonModel {
	
	protected $_validate = array(
		array('sequence_name', 'require', '序列名称不能为空！', 1, 'regex', 3),
		array('sequence_degree', 'require', '序列级别不能为空！', 1, 'regex', 3),
		array('sequence_number', 'require', '序列代码不能为空！', 1, 'regex', 3),
		array('sequence_name', '1,255', '序列名称的值最长不能超过 255 个字符！', 2, 'length', 3),
		array('sequence_degree', 'number', '必须是一个整数！', 2, 'regex', 3),
	);
}
?>