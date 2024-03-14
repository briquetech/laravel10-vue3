@extends('layouts.app')

@section('content')
<?php $objectIdVal = '';?>
@isset($objectId)
	<?php $objectIdVal = (strlen($objectId) > 0?'id="'.$objectId.'"':"");?>
@endisset
<?php $paramVal1 = '';?>
@isset($param1)
	<?php $paramVal1 = 'param1="'.(is_array($param1)?json_encode($param1):$param1).'"';?>
@endisset
<?php $paramVal2 = '';?>
@isset($param2)
	<?php $paramVal2 = 'param2="'.(is_array($param2)?json_encode($param2):$param2).'"';?>
@endisset
<?php $paramVal3 = '';?>
@isset($param3)
	<?php $paramVal3 = 'param3="'.(is_array($param3)?json_encode($param3):$param3).'"';?>
@endisset

<{{ $component }} {!! $paramVal1 !!} {!! $paramVal2 !!} {!! $paramVal3 !!} {!! $objectIdVal !!}></{{ $component }}>

@endsection
