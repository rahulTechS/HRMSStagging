@extends('layouts.hrmLayout')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
     <link href="{{ asset('hrm/css/bootstrap-datepicker.css')}}" rel="stylesheet">
	 <script src="{{ asset('hrm/js/newJquery/jquery-3.5.1.min.js')}}"></script>
	 <script src="{{ asset('hrm/js/bootstrap-datepicker.js')}}"></script>
	 <script src="{{ asset('hrm/js/bootstrap-select/1.10.0/js/bootstrap-select.min.js')}}"></script>
  
  <link href="{{ asset('hrm/js/bootstrap-select/1.10.0/css/bootstrap-select.min.css')}}" rel="stylesheet">
	
  
<script>
$jenbdCards = $.noConflict();
</script>
<style>
.tab .nav-tabs{
    border-bottom:0 none !important;
    margin-top: 20px!important;
}

	.tab .tab-content h3{margin-top: 0;}
	.tab .nav-tabs li{width: 20%!important;}


.tab .tab-content{
    color: #353535;
    padding: 25px 20px;
    border-radius: 0 0 8px 8px;
    box-shadow: 8px 12px 25px 2px rgb(0 0 0 / 40%);
    background: #ebecf0;
    float: left;
    width: 100%!important;
    margin-top: 0;
}
	.mobile span, .iban span, .salary span{    position: absolute;
    top: 35px;
    left: 24px;
    font-weight: bold;
    color: #144282;
    border-right: 2px solid #144282;
    padding-right: 6px;
    font-size: 11px;
    display: inline-table;}
	.graph-section .form-control, .graph-section .form-control .dropdown-toggle, .graph-section .form-control .dropdown-toggle:hover{    border-radius: 0;
    border: none;
    background-color: #dddddd;min-height: 40px;line-height: 24px;}
	, .graph-section .form-control .dropdown-toggle span{font-size: 11px;}
	.graph-section .mobile .form-control, .graph-section .iban .form-control, .graph-section .salary .form-control{padding-left: 60px;}
	.graph-section .btn-group.bootstrap-select.form-control{background-color: transparent;
    min-height: 40px;}
	.bootstrap-select.btn-group .dropdown-toggle .filter-option{    font-size: 11px;}
	.inner-button, .inner-button:hover{    padding: 7px 42px !important;
    background: #033479 !important;
    font-weight: bold !important;
    color: #fff !important;
    margin: 0 15px;
    border-radius: 0;
    float: right;}
	.validation{      font-size: 11px !important;
    letter-spacing: 0;
    color: red !important;
    position: absolute !important;
    border: none !important;
    bottom: -19px;
    top: inherit !important;
    left: 16px !important;}
input[type=text], select, .selectpicker.form-control, textarea, input[type=email], .btn.dropdown-toggle, .btn.dropdown-toggle:hover {
    width: 100%;
    padding: 8px;
border: 2px solid #059ec7;
    border-radius: 4px;
    box-sizing: border-box;
    resize: vertical;    height: auto;    background-color: white;
}
	.breadcrumb{background-color: #337ab7;}
	.breadcrumb>li{    font-weight: bold;
    font-size: 11px;}
	.breadcrumb>li>a{    color: #fafafa;}
	  table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td {
      white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 138px;
    border: none;
    border-top: 2px solid #f5f7f9;
}
	  .input-group>.input-group-prepend>.input-group-text{
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
	hr{    float: left;
    width: 100%;
    border-width: 3px;}
	  .input-group-prepend{    position: absolute;
    bottom: 7px;}
.input-group-text {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    padding: 0.375rem 0.75rem;
    margin-bottom: 0;
    font-size: 1.4rem;
    font-weight: bold;
    line-height: 1.5;
    color: #495057;
    text-align: center;
    white-space: nowrap;
    border-right: 2px solid #ced4da;
    border-radius: 0.25rem;
}
	  .aed-cont input{padding-left: 51px;}
	.input-group-addon{background: transparent;
    position: absolute;
    width: 100%;
    height: 58%;
    border: none;
    z-index: 999999;
    top: 26px;}
	.input-group.date{    margin: 0;
    width: 100%;}
	.panel-body {
    min-height: auto;
}

.marked-sandwich, .marked-present, .marked-leave, .not-marked, .marked-holiday, .marked-late{font-weight: bold;margin: 0 auto;display: block;text-align: center;}
	.marked-late{    background: brown;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}
.marked-sandwich{background: red;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}
.marked-present{ background: forestgreen;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}
.marked-leave{ color: #a94442;white-space: nowrap;
    display: inline;
}
.not-marked{ color: black;}
.marked-holiday{background: blue;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}	
	#errorPopup .modal-header{    padding: 5px;    background: red;}
		  #errorPopup .modal-header i{ 
    color: #fff;

    font-size: 20px;
float: left;
    padding: 8px 15px;}
	  #errorPopup .modal-title{
    font-weight: bold;
    font-size: 25px;color: #fff;}
	  #errorPopup #errorTxt{    text-align: center;
    font-size: 21px;
    margin-top: 5px; margin-bottom: 5px;}
	#errorPopup .modal-content{    overflow: hidden;}
	  #errorPopup .modal-footer{text-align: center;}
	  #errorPopup button{width: 150px;
    border-radius: 50px;
    padding: 7px;
    font-size: 18px;
    background: #006985;
    border: none;    color: #daf6ff;}
	#errorPopup .modal-body{    padding: 5px;    text-align: center;}
	#errorPopup .modal-body i{padding: 15px;
    background: red;
    border-radius: 50%;
    color: #fff;}
	  #errorPopup button:hover{color: #fff; border: none;}
	.d-md-flex {
    display: -webkit-box !important;
    display: -ms-flexbox !important;
    display: flex !important;
}		
td{    vertical-align: middle !important;}
.marked-leave .red, .marked-leave .blue, .marked-leave .green, .marked-leave .orange{width: 15px;
    height: 15px;
    display: inline-block;
    border-radius: 50%;
    position: relative;
    top: 1px;}
.marked-leave .red{    background: red;}
.marked-leave .blue{    background: blue;}
.marked-leave .green{    background: green;}
.marked-leave .orange{    background: orange;}
	.panel-body {
    overflow: visible;
}
	  #incentivebreakUpAsPerCaption{    text-align: center;
    font-weight: bold;
    font-size: 22px;}
	.bootstrap-select .btn:focus{    outline: none !important;background-color: #ffffff;}
	.form-control .selectpicker {    width: 100%;
    padding: 8px;
    border: 2px solid #059ec7;
    border-radius: 4px;
    box-sizing: border-box;
    resize: vertical;}
	.form-control .selectpicker:hover{background-color: #ffffff;}
	#example_wrapper .col-sm-12{    overflow-x: scroll;
  }

	table.dataTable thead .sorting:after{display: none;}
	table.dataTable thead>tr>th.sorting{    padding-right: 8px;white-space: nowrap;}
	table.dataTable thead .sorting, table.dataTable thead .sorting_asc, table.dataTable thead .sorting_desc, table.dataTable thead .sorting_asc_disabled, table.dataTable thead .sorting_desc_disabled {
    color: #fff;
    background-color: #212529;
    border-color: #32383e;
}
	table.table-bordered.dataTable th, table.table-bordered.dataTable td {
    padding: 15px 8px;
}
	.leave-approve a {    text-align: center;
    margin: 0 auto;
    background: green;
    color: #fff;
    font-weight: bold;
    padding: 10px;
    border-radius: 50px;}
	.leave-approve a:hover{text-decoration: none;}

	.dinamic{font-size: 20px;color: #000;}
	.button-design{    padding: 0;
    list-style: none;
    display: block;
    text-align: right;}
	.button-design li{    display: inline-block;
    margin: 0 10px;}
	.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}
	.btn-primary:hover, .btn-primary:focus {
    color: #fff;
    background-color: #0069d9;
    border-color: #0062cc;
}
	.btn-dark {
    color: #fff;
    background-color: #343a40;
    border-color: #343a40;
}
	.btn-dark:hover, .btn-dark:focus {
    color: #fff;
    background-color: #23272b;
    border-color: #1d2124;
}
	.btn-info {
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}
	.btn-info:hover, .btn-info:focus {
    color: #fff;
    background-color: #138496;
    border-color: #117a8b;
}
	.form-group label {    background: #fff;
    position: relative;
    top: 13px;
    right: -8px;
    padding: 0px 13px;    z-index: 99;}
	.graph-section .form-group label{    position: static;
    padding: 0;    font-size: 11px;}
	#example_wrapper svg{    width: 30px;
    height: 30px;}
	#example_wrapper svg g{    fill: red;}
			.dropdown-menu {
    z-index: 99 !important;
}
	#emp_txt span{    position: absolute;
    white-space: nowrap;
    right: -133px;
    top: 34px; z-index: 9;}
	#emp_txt span a{color: red;
    font-weight: bold;
    text-decoration: underline;   }
	.viewer{    border-bottom: 1px solid #ddd;
    margin-bottom: 15px;}
	.viewer ul{    list-style: none;
    padding: 0;
    display: block;
    text-align: center;}
	.viewer ul li{    display: inline-block;
    padding: 0 14px;
    font-weight: bold;
    border-right: 1px solid #ddd;}
	.viewer ul li:last-child{border-right: none;}
	  em{color: red;margin-left: 4px;}
	 a.close {    position: relative;
    top: -25px;
    right: 13px;
    opacity: 1;
    color: #fff;}
	  #errorContent{    margin: 0;
    text-align: center;
    padding: 10px;
    font-size: 17px;
    letter-spacing: 1px;}
		@media  only screen and (max-width: 600px) {
.panel-body {
    min-height: 442px;
}
			.viewer ul{    text-align: left;}
			.viewer ul li{    width: 49%;
    margin: 10px 0;
    font-size: 11px;}
		div.dataTables_wrapper div.dataTables_length label{    width: 100%;}
		div.dataTables_wrapper div.dataTables_filter{    text-align: left;}
			.button-design li{display: block;}
}
		
.custom-file-upload-hidden {
  display: none;
  visibility: hidden;
  position: absolute;
  left: -9999px;
}

.custom-file-upload {
  display: block;
  width: auto;
  font-size: 11px;
  margin-top: 30px;
}
.custom-file-upload label {
  display: block;
  margin-bottom: 5px;
}

.file-upload-wrapper {
  position: relative;
  margin-bottom: 20px;
}

.file-upload-input:hover, .file-upload-input:focus {

  outline: none;
}

.file-upload-button {
    cursor: pointer;
    display: inline-block;
    color: #fff;
    font-size: 11px;
    text-transform: uppercase;
    padding: 10px;
    border: none;
    margin-left: -1px;
    background-color: #057ccc;
    float: left;
    -moz-transition: all 0.2s ease-in;
    -o-transition: all 0.2s ease-in;
    -webkit-transition: all 0.2s ease-in;
    transition: all 0.2s ease-in;
    position: absolute;
    right: 0;
	    border-radius: 0px 4px 4px 0px;
}
.file-upload-button:hover {
  background-color: #057ccc;
}
		
	.upload span {
        position: absolute;
    font-size: 11px;
    color: red;
    font-weight: bold;
    letter-spacing: .5px;
}
	.upload .size-des{    bottom: 0;}
	
	
	
	
	.panel-default>.panel-heading{position: static;width: 100%;    background: #033479;
    border-radius: 0;    display: flex;    padding: 15px 14px;
}
	.panel-heading .col-lg-6{float: none;    font-size: 11px;    font-weight: normal;}
	.panel-heading{    animation: inherit;}
	
	.panel-default {
    border-radius: 0;
}

	#page-wrapper
	.filter-section{    background: #043880;}
	.filter-section ul{background: #043880;    margin: 0;
    padding: 15px;}
	.filter-section ul li{display: inline-block;}
	.filter-section ul li a{    padding: 7px 10px;
    background: #fff;
    font-weight: normal;
    color: #033479;
    margin: 0 3px; font-size: 11px;}
	.filter-section ul li a:hover{text-decoration: none;}
	#page-wrapper{    padding: 62px 0 0 0 !important;}
	#page-wrapper > .container-fluid{    padding: 0;}
	.custom-design{    padding-top: 0 !important;}
</style>
<style>
	.empty_message{    text-align: center;
    font-size: 25px;
    color: red;
    font-weight: bold;}
	.no-data{    position: relative;
    margin-right: 12px;}
	.no-data .fa-times{    position: absolute;
    bottom: -3px;
    right: -4px;
    font-size: 11px;
    background: #fff;
    border-radius: 50px;
    padding: 2.5px;
    width: 15px;
    height: 15px;}
input[type=text], select, .selectpicker.form-control, textarea, input[type=email] {
    width: 100%;
    padding: 8px;
    border-radius: 0;
    border: none;
    background-color: #dddddd;
    box-sizing: border-box;
    resize: vertical;    height: auto;
}
	  table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td {
    font-size: 11px;
    font-weight: bold; text-align: left;
}
	  .input-group>.input-group-prepend>.input-group-text{
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
	.body-section .col-lg-3 {
    min-height: 80px;
}
	.body-section{    padding: 5px 20px;}
	  .input-group-prepend{    position: absolute;
    bottom: 7px;}
.input-group-text {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    padding: 0.375rem 0.75rem;
    margin-bottom: 0;
    font-size: 1.4rem;
    font-weight: bold;
    line-height: 1.5;
    color: #495057;
    text-align: center;
    white-space: nowrap;
    border-right: 2px solid #ced4da;
    border-radius: 0.25rem;
}
	  .aed-cont input{padding-left: 51px;}
	.input-group-addon{background: transparent;
    position: absolute;
    width: 100%;
    height: 58%;
    border: none;
    z-index: 999999;
    top: 26px;}
	.input-group.date{    margin: 0;
    width: 100%;}
	.panel-body {


    min-height: auto;
}

	.panel-default{    float: left;
    width: 100%;}
		.panel-upper{   float: none;
    width: 98%;
    margin: 0 auto;}
.marked-sandwich, .marked-present, .marked-leave, .not-marked, .marked-holiday, .marked-late{font-weight: bold;margin: 0 auto;display: block;text-align: center;}
	.marked-late{    background: brown;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}
.marked-sandwich{background: red;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}
.marked-present{ background: forestgreen;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}
.marked-leave{ color: #a94442;white-space: nowrap;
    display: inline;
}
.not-marked{ color: black;}
.marked-holiday{background: blue;
    color: #fff;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    padding: 6px;}	
	.errorPopup .modal-header{    padding: 5px;    background: red;}
		  .errorPopup .modal-header i{ 
    color: #fff;

    font-size: 20px;
float: left;
    padding: 8px 15px;}
	  .errorPopup .modal-title{
    font-weight: bold;
    font-size: 25px;color: #fff;}
	  .errorPopup #errorTxt{    text-align: center;
    font-size: 21px;
    margin-top: 5px; margin-bottom: 5px;}
	.errorPopup .modal-content{    overflow: hidden;}
	  .errorPopup .modal-footer{text-align: center;}
	  .errorPopup button{width: 150px;
    border-radius: 50px;
    padding: 7px;
    font-size: 18px;
    background: #006985;
    border: none;    color: #daf6ff;}
	.errorPopup .modal-body{    padding: 5px;}
	.errorPopup .modal-body i{padding: 13px;
    font-size: 19px;
    background: red;
    border-radius: 50%;
    color: #fff;}
	  .errorPopup button:hover{color: #fff; border: none;}
	.d-md-flex {
    display: -webkit-box !important;
    display: -ms-flexbox !important;
    display: flex !important;
}		
td{    vertical-align: middle !important;}
.marked-leave .red, .marked-leave .blue, .marked-leave .green, .marked-leave .orange{width: 15px;
    height: 15px;
    display: inline-block;
    border-radius: 50%;
    position: relative;
    top: 1px;}
.marked-leave .red{    background: red;}
.marked-leave .blue{    background: blue;}
.marked-leave .green{    background: green;}
.marked-leave .orange{    background: orange;}
	.panel-body {
    overflow: visible;
}
	  #incentivebreakUpAsPerCaption{    text-align: center;
    font-weight: bold;
    font-size: 22px;}
	.bootstrap-select .btn:focus{    outline: none !important;background-color: #ffffff;}
	.form-control .selectpicker {    width: 100%;
    padding: 8px;
    border: 2px solid #059ec7;
    border-radius: 4px;
    box-sizing: border-box;
    resize: vertical;}
	.form-control .selectpicker:hover{background-color: #ffffff;}
	#example_wrapper .col-sm-12{    overflow-x: scroll;
  }

	table.dataTable thead .sorting:after{display: none;}
	table.dataTable thead>tr>th.sorting{    padding-right: 8px;white-space: nowrap;}
	table.dataTable thead .sorting, table.dataTable thead .sorting_asc, table.dataTable thead .sorting_desc, table.dataTable thead .sorting_asc_disabled, table.dataTable thead .sorting_desc_disabled {
    color: #fff;
    background-color: #212529;
    border-color: #32383e;
}
	table.table-bordered.dataTable th, table.table-bordered.dataTable td {
    padding: 15px 8px;
}
	.leave-approve a {    text-align: center;
    margin: 0 auto;
    background: green;
    color: #fff;
    font-weight: bold;
    padding: 10px;
    border-radius: 50px;}
	.leave-approve a:hover{text-decoration: none;}

	.dinamic{font-size: 20px;color: #000;}
	.button-design{    padding: 0;
    list-style: none;
    display: block;
    text-align: right;}
	.button-design li{    display: inline-block;
    margin: 0 10px;}
	.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}
	.btn-primary:hover, .btn-primary:focus {
    color: #fff;
    background-color: #0069d9;
    border-color: #0062cc;
}
	.btn-dark {
    color: #fff;
    background-color: #343a40;
    border-color: #343a40;
}
	.btn-dark:hover, .btn-dark:focus {
    color: #fff;
    background-color: #23272b;
    border-color: #1d2124;
}
	.btn-info {
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}
	.btn-info:hover, .btn-info:focus {
    color: #fff;
    background-color: #138496;
    border-color: #117a8b;
}
	.form-group label {    background: #fff;
    position: relative;
    top: 13px;
    right: -8px;
    padding: 0px 13px;    z-index: 99;}
	#example_wrapper svg{    width: 30px;
    height: 30px;}
	#example_wrapper svg g{    fill: red;}
			.dropdown-menu {
    z-index: 99 !important;
}
	#emp_txt span{    position: absolute;
    white-space: nowrap;
    right: -133px;
    top: 34px; z-index: 9;}
	#emp_txt span a{color: red;
    font-weight: bold;
    text-decoration: underline;   }
	.viewer{    border-bottom: 1px solid #ddd;
    margin-bottom: 15px;}
	.viewer ul{    list-style: none;
    padding: 0;
    display: block;
    text-align: center;}
	.viewer ul li{    display: inline-block;
    padding: 0 14px;
    font-weight: bold;
    border-right: 1px solid #ddd;}
	.viewer ul li:last-child{border-right: none;}
	  em{color: red;margin-left: 4px;}
	 a.close {    position: relative;
    top: -25px;
    right: 13px;
    opacity: 1;
    color: #fff;}
	  #errorContent{    margin: 0;
    text-align: center;
    padding: 10px;
    font-size: 17px;
    letter-spacing: 1px;}
		@media  only screen and (max-width: 600px) {
.panel-body {
    min-height: 442px;
}
			.viewer ul{    text-align: left;}
			.viewer ul li{    width: 49%;
    margin: 10px 0;
    font-size: 11px;}
		div.dataTables_wrapper div.dataTables_length label{    width: 100%;}
		div.dataTables_wrapper div.dataTables_filter{    text-align: left;}
			.button-design li{display: block;}
}
  .blockTarget:first-child{border-top: none;}
	  .blockTarget{     float: left;
    width: 94%;
    margin: 0 3%;
    border-top: 2px dashed #cccccc;
}
.blockTargetClass{
    padding: 10px 54px;
    background: red;
    color: #fff;
    background-color: #31b0d5;
    border-color: #269abc;
    float: right;
    border-radius: 5px;
    margin: 20px 0;
    font-weight: bold;
	font-size: 11px;
	cursor:pointer;
}
	.text-center{text-align: center !important;}
	
	.close{color: #fff;
    opacity: 1;}
	.text-right{text-align: right !important;}
	
	.body-section .heading-custom{    margin-top: 24px;
    font-size: 22px;
    color: #14297b;
    text-transform: uppercase;
    letter-spacing: 1px;}
	#balanceLeave{    display: block;
    border-top: 1px solid #ababab;
    float: left;
    padding: 20px 0;
    margin: 10px 0;
    padding-bottom: 0;
    width: 100%;
    margin-bottom: 0;
    background: #f4f4f4;}
	
.removeMe{
margin-top: 36px;
}
	.form-group {
    margin-bottom: 20px;    margin-top: 10px;
}
	.body-section p {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: bold;
    color: #8c8c8c;
    letter-spacing: 1px;
    margin-bottom: 3px;
}
	.body-section h3 {
    margin-top: 0;
    font-weight: normal;
    font-size: 19px;
    margin-bottom: 5px;
}
	.jonus-cut{    font-size: 14px !important;}
	.jonus-heading{font-weight: normal !important;
    font-size: 11px !important;}
	.img-sec img {
    width: 50px;
    margin-right: 12px;
}
	.self-info h4 {
    font-weight: bold;
    color: #737373;
}
	.border-left{border-left: 3px solid #eee;}
	.pt-2{    padding-top: 2rem;}
	.form-check .checkbox{    height: 27px;
    width: 27px;}
	.panel-body{    padding-top: 5px;}
	.top-heading{      margin-bottom: 12px;
    text-align: center;
    margin-top: 0;
    font-weight: bold;
    font-size: 20px;}
	.modal-dialog{width: 36% !important;}
	.detail-report .modal-dialog{    width: 90% !important;}
	.docClassCollection td{
		font-size:11px !important;
	}
	.sorting {
		font-size:11px !important;
	}
	.scroll{    overflow-x: scroll;}
		.top-accord{padding-bottom: 20px;}
	.show-accord{    height: auto;}
	.top-accord .border-bottom{opacity: 0; display: none;}
	.show-accord .border-bottom{
		opacity: 1;transition: opacity 1s linear; display: block;}
	.accord, .accord:hover {
    float: right;
}
	.dataTables_length label{    font-weight: normal;
    text-align: left;
    white-space: nowrap; font-size: 11px;}
	.dataTables_length select.input-sm{    width: 75px;
    display: inline-block;}
	
	.pagination>li>a, .pagination>li>span{    background: transparent;
    border: none;}
	.pagination>.active>a, .pagination>.active>a:focus, .pagination>.active>a:hover, .pagination>.active>span, .pagination>.active>span:focus, .pagination>.active>span:hover{ color: #fc7100;background: transparent;    background-color: transparent;
    border: none;}
	.pagination>li>a, .pagination>li>span{background: transparent;font-weight: normal;
    border: none; color: #000;    background-color: none; font-size: 11px;}
	{background: transparent;
    border: none;}
	.table>caption+thead>tr:first-child>td, .table>caption+thead>tr:first-child>th, .table>colgroup+thead>tr:first-child>td, .table>colgroup+thead>tr:first-child>th, .table>thead:first-child>tr:first-child>td, .table>thead:first-child>tr:first-child>th{    border: none;vertical-align: baseline; font-size: 11px;}
	table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td{    white-space: nowrap;    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 138px;    border: none;    border-top: 2px solid #f5f7f9;}
	table.dataTable thead .sorting, table.dataTable thead .sorting_asc, table.dataTable thead .sorting_desc, table.dataTable thead .sorting_asc_disabled, table.dataTable thead .sorting_desc_disabled{    background-color: transparent; border: none; color: #000}
	#jonus_list{    border: none;    border-top: 2px solid #f5f7f9;}
	.table-striped>tbody>tr{    background-color: #fff !important; cursor: pointer;}
	.table-striped>tbody>tr:hover{  background-color: #e1e1e1 !important;}
	.table-striped>tbody>tr.selected{background: #033479 !important;}
	.table-striped>tbody>tr.selected td{    color: #fff;}
	.table-striped>tbody>tr.selected .detail{    background: #fff;
    color: #033479;}
	.detail{    padding: 10px 20px;
    background: #033479;
    color: #fff;}
	.detail:hover{color: #fff; text-decoration: none;}
	.detail i{margin-left: 5px;}
	table.dataTable thead>tr>th.sorting{padding: 15px 8px;}
	.dataTables_length, .dataTables_info, .pagination{    margin: 0 0 !important;}
	.company-detail{    display: flex;
    background: #033479;    padding: 15px;}
	.company-detail .btn-group{    width: 60% !important;
    margin-left: 5px !important;
    border-radius: 0 !important;}
	.company-detail .btn-group .btn{    border: none;
    border-radius: 0;
    background: #f2f3f7;
    font-size: 11px;}
	.company-detail label{    color: #fff;
    font-size: 11px;}
	.company-detail .accord{padding: 7px 10px;
    background: #fff;
    font-weight: bold;
    color: #033479;
    margin: 0 3px;
    border-radius: 0;    float: left;}
	.dropdown-menu>li>a{font-size: 11px;}
	.loadingImage{
    width: 100%;
    text-align: center;
    position: fixed;
    z-index: 9999;
    background: rgba(0,0,0,.7);
    top: 0;
    height: 100vh;
    padding-top: 18%;    opacity: 1 !important;
	}
	.bootstrap-select.btn-group .dropdown-menu li a{font-weight: bold;    text-transform: capitalize;}
	 .loadingImage img{    width: 66px;}
	.div-open{display: block !important;}
	.pop-button{    padding: 30px;
    display: block;
    list-style: none;
    padding-bottom: 0;
    padding-top: 0;}
	.pop-button li {
    display: inline-block;
    width: 45%;
    margin: 9px 9px;
    text-align: center;
}	
	.pop-button .inner-button{    float: none;
    width: 100%;
    display: block;
    margin: 0;}
	.pop-button .inner-button:hover{text-decoration: none;background: #012d6a;}
	.filter-inner{    width: 98%;
    margin: 15px auto;
    background: #033479;
    padding: 15px 14px;
    margin-bottom: 0;}
	.filter-inner ul{    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;}
	.filter-inner ul li a{    padding: 5px 15px;
    background: #f0f0f0;
    border-radius: 15px;
    margin-right: 12px;
    font-weight: bold;}
	.filter-inner ul li a span{    color: red;}
</style>
<style>
	.alert {margin-bottom: 50px;}
	.filter-body{
		min-height:100px !important;
	}
	input[type=text], select, textarea {
    width: 100%;
    padding: 8px;
    border: 2px solid #059ec7;
    border-radius: 4px;
    box-sizing: border-box;
    resize: vertical;
		height: auto;
}
	.form-group label {
    background: #fff;
    position: relative;
    top: 13px;
    right: -8px;
    padding: 0px 13px;    z-index: 99;
}
	.btn-dark {
    color: #fff;
    background-color: #343a40;
    border-color: #343a40;
}
	.btn-dark:hover, .btn-dark:focus {
    color: #fff;
    background-color: #23272b;
    border-color: #1d2124;
}
	select:disabled {
    opacity: .5;
    background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAIklEQVQIW2NkQAKrVq36zwjjgzhhYWGMYAEYB8RmROaABADeOQ8CXl/xfgAAAABJRU5ErkJggg==) repeat;border: 2px solid #858585;
}
	.arrow-down {    width: 0;
    height: 0;
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-top: 20px solid #fff;
    position: absolute;
    bottom: -16px;
    left: 47px;
}
	.pop{position: absolute;
    top: -205px;
    display: none;
    width: 350px;
    background: #fff;
    -webkit-box-shadow: 0px 0px 29px 0px rgb(0 0 0 / 33%);
    -moz-box-shadow: 0px 0px 29px 0px rgba(0,0,0,0.33);
    box-shadow: 0px 0px 29px 0px rgb(0 0 0 / 33%);
    left: -51px;    margin: 0 !important;
    padding: 20px 0;    border-top: 8px solid #14297b;}
	.progress-container .pop.body-section p{     position: static;
    margin: 0;
    text-align: left;
    margin-bottom: 5px;}
	.progress-container .pop.body-section h3{    font-size: 11px;
    margin-bottom: 19px;}
	.box-hover .pop{display: block !important;}
	table.dataTable thead>tr>th.sorting {
    padding-right: 8px;
    white-space: nowrap;
}
	.body-section .font-detail{font-size: 11px !important;}
	.action-tool span{    display: inline-block;
    text-align: center;
 
    height: 35px;
    padding: 7px;
    border-radius: 50px;
       margin-right: 5px;
    width: 45%;}
	.action-tool span:first-child{    background: green;}
	.action-tool span:last-child{    background: red;}
		.action-tool span:first-child a, .action-tool span:last-child a{    color: #FFFFFF;}
	.modal-dialog {
    width: 75%;

}

	
	
	
	

.horizontal-list {
  margin: 0;
  padding: 0;
  list-style-type: none;
}
.horizontal-list li {
  float: left;
}


.arrow-btn-container {
  position: relative;
}
.arrow-btn {
  position: absolute;
  display: block;
  width: 60px;
  height: 60px;
  -webkit-transition: background 0.3s;
  transition: background 0.3s;
}
.arrow-btn:hover {
  text-decoration: none;
}
.arrow-btn.left {
  border-top-left-radius: 5px;
}
.arrow-btn.right {
  border-top-right-radius: 5px;
  right: 0;
  top: 0;
}
.arrow-btn .icon {
  display: block;
  font-size: 18px;
  border: 2px solid #fff;
  border-radius: 100%;
  line-height: 17px;
  width: 21px;
  margin: 20px auto;
  text-align: center;
}
.arrow-btn.left .icon {
  padding-right: 2px;
}

.profile-picture {
  border-radius: 100%;
  overflow: hidden;
  -webkit-box-sizing: content-box;
  -moz-box-sizing: content-box;
  box-sizing: content-box;
}
.big-profile-picture {
  margin: 0 auto;
  border: 5px solid #ffffff;
  width: 150px;
  height: 150px;    position: relative;
    top: 10px;
}
.small-profile-picture {
  border: 2px solid #50597b;
  width: 40px;
  height: 40px;
}

/** MAIN CONTAINER **/

.main-container {
  font-family: "Ubuntu", sans-serif;
  width: 950px;
  height: 1725px;
  margin: 6em auto;
}
/*********************************************** HEADER ***********************************************/
header {
  height: 80px;
}
.header-menu {
  font-size: 17px;
  line-height: 80px;
}
.header-menu li {
  position: relative;
  -webkit-transform: translateZ(0); /** To avoid a flash when hover messages **/
}
.menu-box-menu li {
  padding: 10px 15px;
  display: block;
  font-size: 17px;
  -webkit-transition: background 0.3s;
  transition: background 0.3s; border-bottom: 4px solid #e7e7e7;
}

	.profile.block .menu-box-menu li{    display: inline-block;
    width: 49%;
    border: none;    color: #fff;}
	.profile.block .menu-box-menu li:last-child{text-align: right;}
	.menu-box-menu li p{    font-size: 11px; }

	.menu-box-menu li h2{    font-size: 11px;     font-weight: bold;  margin-top: 6px;color: #14297b;margin-bottom: 0;}
		.menu-box-menu li p{    margin-bottom: 0;}
	.profile.block .menu-box-menu li h2{  color: #fff;    font-size: 11px;}
	.profile.block .menu-box-menu li p{  color: #fff;    font-size: 11px;}
	
	.scnd-font-color{    font-weight: bold;
    color: #fff;
    text-decoration: underline;
    font-size: 11px;
    margin: 5px 5px;
    border-bottom: 2px solid #fff;
    padding-bottom: 7px;}
		.header-menu-tab span{ display: block;}

.header-menu-tab .icon {
  padding-right: 15px;
}
.header-menu-number {
  position: absolute;
  line-height: 22px;
  padding: 0 6px;
  font-weight: 700;
  background: #e64c65;
  border-radius: 100%;
  top: 15px;
  right: 2px;
  -webkit-transition: all 0.3s linear;
  transition: all 0.3s linear;
}
.header-menu li:hover .header-menu-number {
  text-decoration: none;
  -webkit-transform: rotate(360deg);
  transform: rotate(360deg);
}
.profile-menu {
  float: right;
  height: 80px;
  padding-right: 20px;
}
.profile-menu p {
  font-size: 11px;
  display: inline-block;
  line-height: 76px;
  margin: 0;
  padding-right: 10px;
}
.profile-menu a {
  padding-left: 5px;
}
.profile-menu a:hover {
  text-decoration: none;
}
.small-profile-picture {
  display: inline-block;
  vertical-align: middle;
}
/** CONTAINERS **/
.left-container, .middle-container, .right-container{
  float: left;
  width: 33%;
}
.block {
  margin-bottom: 25px;
  background: #f4f4f4;
  border-radius: 5px;
	padding-bottom: 10px;    box-shadow: 1px 4px 5px -2px #888888;       background-image: linear-gradient(#becbff, #f4f4f4);
   
}
	.profile.block{  background-image: linear-gradient(#4562d8, #5474f1);}
	.menu-box-menu{list-style-type: none;
    margin: 0;
    padding-left: 0;}
/******************************************** LEFT CONTAINER *****************************************/
.left-container {
}

.menu-box .titular {
  background: #11a8ab;
}
.menu-box-menu .icon {
  display: inline-block;
  vertical-align: top;
  width: 28px;
  margin-left: 20px;
  margin-right: 15px;
}
.menu-box-number {
  width: 36px;
  line-height: 22px;
  background: #50597b;
  text-align: center;
  border-radius: 15px;
  position: absolute;
  top: 15px;
  right: 15px;
  -webkit-transition: all 0.3s;
  transition: all 0.3s;
}

.menu-box-tab {
  line-height: 60px;
  display: block;
  border-bottom: 1px solid #1f253d;
  -webkit-transition: background 0.2s;
  transition: background 0.2s; padding: 0 15px;
}
.menu-box-tab:hover {
  background: #50597b;
  border-top: 1px solid #1f253d;
  text-decoration: none;
}
.menu-box-tab:hover .icon {
  color: #fff;
}
.menu-box-tab:hover .menu-box-number {
  background: #e64c65;
}
.donut-chart-block {
  height: 434px;
}
.donut-chart-block .titular {
  padding: 10px 0;
}
.donut-chart {
  height: 270px;
}
/******************************************
				DONUT-CHART by @kseso https://codepen.io/Kseso/pen/phiyL
				******************************************/
.donut-chart {
  position: relative;
  width: 200px;
  height: 200px;
  margin: 0 auto 2rem;
  border-radius: 100%;
}
p.center-date {
  background: #394264;
  position: absolute;
  text-align: center;
  font-size: 28px;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 130px;
  height: 130px;
  margin: auto;
  border-radius: 50%;
  line-height: 35px;
  padding: 15% 0 0;
}
.center-date span.scnd-font-color {
  line-height: 0;
}
.recorte {
  border-radius: 50%;
  clip: rect(0px, 200px, 200px, 100px);
  height: 100%;
  position: absolute;
  width: 100%;
}
.quesito {
  border-radius: 50%;
  clip: rect(0px, 100px, 200px, 0px);
  height: 100%;
  position: absolute;
  width: 100%;
  font-family: monospace;
  font-size: 1.5rem;
}
#porcion1 {
  -webkit-transform: rotate(0deg);
  transform: rotate(0deg);
}

#porcion1 .quesito {
  background-color: #e64c65;
  -webkit-transform: rotate(76deg);
  transform: rotate(76deg);
}
#porcion2 {
  -webkit-transform: rotate(76deg);
  transform: rotate(76deg);
}
#porcion2 .quesito {
  background-color: #11a8ab;
  -webkit-transform: rotate(140deg);
  transform: rotate(140deg);
}
#porcion3 {
  -webkit-transform: rotate(215deg);
  transform: rotate(215deg);
}
#porcion3 .quesito {
  background-color: #4fc4f6;
  -webkit-transform: rotate(113deg);
  transform: rotate(113deg);
}
#porcionFin {
  -webkit-transform: rotate(-32deg);
  transform: rotate(-32deg);
}
#porcionFin .quesito {
  background-color: #fcb150;
  -webkit-transform: rotate(32deg);
  transform: rotate(32deg);
}
/******************************************
				END DONUT-CHART by @kseso https://codepen.io/Kseso/pen/phiyL
				******************************************/
.os-percentages {
  padding-top: 36px;
}
.os-percentages li {
  width: 75px;
  border-left: 1px solid #394264;
  text-align: center;
  background: #50597b;
}
.os {
  margin: 0;
  padding: 10px 0 5px;
  font-size: 11px;
}
.os.ios {
  border-top: 4px solid #e64c65;
}
.os.mac {
  border-top: 4px solid #11a8ab;
}
.os.linux {
  border-top: 4px solid #fcb150;
}
.os.win {
  border-top: 4px solid #4fc4f6;
}
.os-percentage {
  margin: 0;
  padding: 0 0 15px 10px;
  font-size: 25px;
}
.line-chart-block {
  height: 400px;
}
.line-chart {
  height: 200px;
  background: #11a8ab;
}
/******************************************
				LINE-CHART by @kseso https://codepen.io/Kseso/pen/phiyL
				******************************************/
.grafico {
  padding: 2rem 1rem 1rem;
  width: 100%;
  height: 100%;
  position: relative;
  color: #fff;
  font-size: 80%;
}
.grafico span {
  display: block;
  position: absolute;
  bottom: 3rem;
  left: 2rem;
  height: 0;
  border-top: 2px solid;
  -webkit-transform-origin: left center;
  transform-origin: left center;
}
.grafico span > span {
  left: 100%;
  bottom: 0;
}
[data-valor="25"] {
  width: 75px;
  -webkit-transform: rotate(-45deg);
  transform: rotate(-45deg);
}
[data-valor="8"] {
  width: 24px;
  -webkit-transform: rotate(65deg);
  transform: rotate(65deg);
}
[data-valor="13"] {
  width: 39px;
  -webkit-transform: rotate(-45deg);
  transform: rotate(-45deg);
}
[data-valor="5"] {
  width: 15px;
  -webkit-transform: rotate(50deg);
  transform: rotate(50deg);
}
[data-valor="23"] {
  width: 69px;
  -webkit-transform: rotate(-70deg);
  transform: rotate(-70deg);
}
[data-valor="12"] {
  width: 36px;
  -webkit-transform: rotate(75deg);
  transform: rotate(75deg);
}
[data-valor="15"] {
  width: 45px;
  -webkit-transform: rotate(-45deg);
  transform: rotate(-45deg);
}

[data-valor]:before {
  content: "";
  position: absolute;
  display: block;
  right: -4px;
  bottom: -3px;
  padding: 4px;
  background: #fff;
  border-radius: 50%;
}
[data-valor="23"]:after {
  content: "+" attr(data-valor) "%";
  position: absolute;
  right: -2.7rem;
  top: -1.7rem;
  padding: 0.3rem 0.5rem;
  background: #50597b;
  border-radius: 0.5rem;
  -webkit-transform: rotate(45deg);
  transform: rotate(45deg);
}
[class^="eje-"] {
  position: absolute;
  left: 0;
  bottom: 0rem;
  width: 100%;
  padding: 1rem 1rem 0 2rem;
  height: 80%;
}
.eje-x {
  height: 2.5rem;
}
.eje-y li {
  height: 25%;
  border-top: 1px solid #777;
}
[data-ejeY]:before {
  content: attr(data-ejeY);
  display: inline-block;
  width: 2rem;
  text-align: right;
  line-height: 0;
  position: relative;
  left: -2.5rem;
  top: -0.5rem;
}
.eje-x li {
  width: 33%;
  float: left;
  text-align: center;
}
/******************************************
				END LINE-CHART by @kseso https://codepen.io/Kseso/pen/phiyL
				******************************************/
.time-lenght {
  padding-top: 22px;
  padding-left: 38px;
}
.time-lenght-btn {
  display: block;
  width: 70px;
  line-height: 32px;
  background: #50597b;
  border-radius: 5px;
  font-size: 11px;
  text-align: center;
  margin-right: 5px;
  -webkit-transition: background 0.3s;
  transition: background 0.3s;
}
.time-lenght-btn:hover {
  text-decoration: none;
  background: #e64c65;
}
.month-data {
  padding-top: 28px;
}
.month-data p {
  display: inline-block;
  margin: 0;
  padding: 0 25px 15px;
  font-size: 11px;
}
.month-data p:last-child {
  padding: 0 25px;
  float: right;
  font-size: 11px;
}
.increment {
  color: #e64c65;
}
.media {
  height: 216px;
}
#media-display {
  position: relative;
  height: 180px;
  background: #787878
    url("https://www.fancinema.com.ar/wp-content/uploads/catwoman1.jpg") center
    top;
}
#media-display .play {
  position: absolute;
  top: 75px;
  right: 32px;
  border: 2px solid #fff;
  border-radius: 100%;
  padding: 2px 5px 2px 9px;
}
#media-display .play:hover {
  border: 2px solid #e64c65;
}
.media-control-bar {
  padding: 6px 0 0 15px;
}
.media-btn,
.time-passed {
  display: inline-block;
  margin: 0;
}
.media-btn {
  font-size: 19px;
}
.media-btn:hover,
.media-btn:hover span {
  text-decoration: none;
  color: #e64c65;
}
.play {
  margin-right: 100px;
}
.volume {
  margin-left: 30px;
}
.resize {
  margin-left: 12px;
}
.left-container .social {
  height: 110px;
}
.left-container .social li {
  width: 75px;
  height: 110px;
}
.left-container .social li .icon {
  text-align: center;
  font-size: 20px;
  margin: 0;
  line-height: 75px;
}
.left-container .social li .number {
  text-align: center;
  margin: 0;
  line-height: 34px;
}
.left-container .social .facebook {
  background: #3468af;
  border-top-left-radius: 5px;
  border-bottom-left-radius: 5px;
}
.left-container .social .facebook .number {
  background: #1a4e95;
  border-bottom-left-radius: 5px;
}
.left-container .social .twitter {
  background: #4fc4f6;
}
.left-container .social .twitter .icon {
  font-size: 18px;
}
.left-container .social .twitter .number {
  background: #35aadc;
}
.left-container .social .googleplus {
  background: #e64c65;
}
.left-container .social .googleplus .number {
  background: #cc324b;
}
.left-container .social .mailbox {
  background: #50597b;
  border-top-right-radius: 5px;
  border-bottom-right-radius: 5px;
}
.left-container .social .mailbox .number {
  background: #363f61;
  border-bottom-right-radius: 5px;
}
/************************************************** MIDDLE CONTAINER **********************************/



.user-name {
  margin: 25px 0 9px;
  text-align: center;    font-size: 28px;font-weight: bold;    color: #ffffff;
}
	.profile-description.id p{   color: #ffffff;    font-size: 21px;}
.profile-description {

  margin: 0 auto;
  text-align: center;
}
.profile-options {
  padding-top: 23px;
}
.profile-options li {
  border-left: 1px solid #1f253d;
}
.profile-options p {
  margin: 0;
}
.profile-options a {
  display: block;
  width: 99px;
  line-height: 60px;
  text-align: center;
  -webkit-transition: background 0.3s;
  transition: background 0.3s;
}
.profile-options a:hover {
  text-decoration: none;
  background: #50597b;
}
.profile-options a:hover.comments .icon {
  color: #fcb150;
}
.profile-options a:hover.views .icon {
  color: #11a8ab;
}
.profile-options a:hover.likes .icon {
  color: #e64c65;
}
.profile-options .icon {
  padding-right: 10px;
}
.profile-options .comments {
  border-top: 4px solid #fcb150;
}
.profile-options .views {
  border-top: 4px solid #11a8ab;
}
.profile-options .likes {
  border-top: 4px solid #e64c65;
}
.middle-container .social {
  height: 205px;
  background: #1f253d;
}
.middle-container .social li {
  margin-bottom: 12px;
}
.middle-container .social a {
  line-height: 60px;
}
.middle-container .social a:hover {
  text-decoration: none;
}
.middle-container .social .titular {
  border-radius: 5px;
}
.middle-container .social .facebook {
  background: #3468af;
  -webkit-transition: background 0.3s;
  transition: background 0.3s;
}
.middle-container .social a:hover .facebook {
  background: #1a4e95;
}
.middle-container .social a:hover .icon.facebook {
  background: #3468af;
}
.middle-container .social .twitter {
  background: #4fc4f6;
  -webkit-transition: background 0.3s;
  transition: background 0.3s;
}
.middle-container .social a:hover .twitter {
  background: #35aadc;
}
.middle-container .social a:hover .icon.twitter {
  background: #4fc4f6;
}
.middle-container .social .googleplus {
  background: #e64c65;
  -webkit-transition: background 0.3s;
  transition: background 0.3s;
}
.middle-container .social a:hover .googleplus {
  background: #cc324b;
}
.middle-container .social a:hover .icon.googleplus {
  background: #e64c65;
}
.middle-container .social .icon {
  float: left;
  width: 60px;
  height: 60px;
  text-align: center;
  font-size: 20px;
  border-bottom-left-radius: 5px;
  border-top-left-radius: 5px;
}
.middle-container .social .icon.facebook {
  background: #1a4e95;
}
.middle-container .social .icon.twitter {
  background: #35aadc;
}
.middle-container .social .icon.googleplus {
  background: #cc324b;
}
	.modal-body .close{
    opacity: 1;

    position: relative;

}
	.close-button a {    width: 50px;
    height: 50px;
    position: absolute;
    right: 50%;
    top: 50%;
    margin-top: -50px;
    margin-right: -50px;

    border-radius: 50px;
    opacity: 1;

}
.close-button a > span {    background-color: #f5a700;
    display: block;
    height: 4px;
    border-radius: 6px;
    position: relative;
    transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
    position: absolute;
    top: 50%;
    margin-top: -3px;
    left: 10px;
    width: 32px;
    display: -webkit-box;
    display: -moz-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-justify-content: space-between;
    justify-content: space-between;
    -moz-justify-content: space-between;
    -ms-justify-content: space-between;
}
.close-button a > span span {
    display: block;
    background-color: #ed7f00;
    width: 4px;
    height: 4px;
    border-radius: 6px;
    transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
    position: absolute;
    left: 0;
    top: 0;
}
	.close-button{    display: block;
    position: absolute;
    right: 61px;
    top: 54px;}
.close-button a > span.left {
  transform: rotate(45deg);
  transform-origin: center;
}
.close-button a > span.left .circle-left {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 0;
}
.close-button a > span.left .circle-right {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 27px;
}
.close-button a > span.right {
  transform: rotate(-45deg);
  transform-origin: center;
}
.close-button a > span.right .circle-left {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 0;
}
.close-button a > span.right .circle-right {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 27px;
}
.close-button a:hover > span {
  background-color: #2faee0;
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
}
.close-button a:hover > span span {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  background-color: #008ac9;
}
.close-button a:hover > span.left .circle-left {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 27px;
}
.close-button a:hover > span.left .circle-right {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 0;
}
.close-button a:hover > span.right .circle-left {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 27px;
}
.close-button a:hover > span.right .circle-right {
  transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
  margin-left: 0;
}
	.modal-heading .modal-title{      font-weight: bold;
    font-size: 23px;
    margin-bottom: 15px;
    padding: 10px 49px;
    display: inline-block;
    background-image: linear-gradient(#14297b, #3051d3);
    color: #fff;
    border-radius: 0px 0px 5px 5px;    margin-top: -2px;}
	.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}
	.btn-primary:hover {
    color: #fff;
    background-color: #0069d9;
    border-color: #0062cc;
}
/********************************************* RIGHT CONTAINER ****************************************/
	.profile-description.id{    font-size: 18px;}
	.profile-description.id p span{    font-weight: bold;    color: #ffffff;}
	.panel-body.bio-graph-info{    min-height: auto;    padding-top: 0;}
	  .form-group{margin-bottom: 15px;
    float: none;
    display: inline-block;
    width: 24.5%;}
	.upload span {
    position: absolute;
}
	.modal-body{padding-top: 0;}
			
#errorPopup .modal-header{    padding: 5px;    background: red;}
		  #errorPopup .modal-header i{ 
    color: #fff;

    font-size: 20px;
float: left;
    padding: 8px 15px;}
	  #errorPopup .modal-title{
    font-weight: bold;
    font-size: 25px;color: #fff;}
	  #errorPopup #errorTxt{    text-align: center;
    font-size: 21px;
    margin-top: 5px; margin-bottom: 5px;}
	#errorPopup .modal-content{    overflow: hidden;}
	  #errorPopup .modal-footer{text-align: center;}
	  #errorPopup button{width: 150px;
    border-radius: 50px;
    padding: 7px;
    font-size: 18px;
    background: #006985;
    border: none;    color: #daf6ff;}
	#errorPopup .modal-body{    padding: 5px;}
	  #errorPopup button:hover{color: #fff; border: none;}
	.d-md-flex {
    display: -webkit-box !important;
    display: -ms-flexbox !important;
    display: flex !important;
}
 #errorContent{    margin: 0;
    text-align: center;
    padding: 10px;
    font-size: 17px;
    letter-spacing: 1px;}
	
/* Style the tab */
.tab {
    float: left;
    border-right: 1px solid #ccc;
    width: 20%;	min-height: 330px;    background: #e3e4e4;
}

/* Style the buttons inside the tab */
.tab button {
    display: block;
    background-color: inherit;
    color: black;
    padding: 22px 16px;
    width: 100%;
    border: none;
    outline: none;
    text-align: left;
    cursor: pointer;
    transition: 0.3s;
    font-size: 17px;
        border-bottom: 3px solid #fff;
}

	.tabcontent a{    padding: 15px;
    margin-top: 15px;}
	table.table-bordered.dataTable tbody td {
    font-weight: normal;
    white-space: nowrap;    font-size: 11px;
}
	table.table-bordered.dataTable tbody td:last-child a{    display: block;}
/* Create an active/current "tab button" class */
.tab button.active, .tab button:hover {
    color: #fff;
    font-weight: bold;
    background: #3051d3;
    border-bottom: 3px solid #fff;
}
	.tabcontent h3{    margin-top: 0;
    margin-bottom: 6px;
    font-weight: bold;
    padding-left: 11px;
    border-bottom: 3px solid #ccc;
    padding-bottom: 10px;}
/* Style the tab content */
.tabcontent {
    float: left;
    padding: 0px 12px;
    width: 80%;
    border-left: none;
}
	.form-group p {
    font-size: 11px;
    font-weight: bold;
    width: 100%;
    padding: 8px;
    border: 2px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    resize: vertical;
}
	html{scroll-behavior: smooth;}
	.top, .top:hover{    position: fixed;
    bottom: 10px;
    right: 10px;
    padding: 15px 18px;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    background: #3051d3;
    color: #fff;}
	#myModal{    padding-top: 94px;}
	#myModal .panel-default{      width: 94%;
    margin: 0 auto;}
	.p-3{padding: 1rem; padding-top: 0;}
	.panel-body .nav-tabs>li.active>a, .panel-body .nav-tabs>li>a:hover{  border: none;
    border-radius: 50px;
    font-weight: bold;
    padding: 12px;
    color: #fff;
    background-image: linear-gradient(#3051d3, #14297b);}
	.panel-body .nav-tabs>li>a{ border: none; font-weight: normal;    padding: 12px;    margin: 0;
    color: #333333;}
	.panel-body .nav-tabs>li{     float: left;
    text-align: center;
    font-size: 11px;}
	button.close{    opacity: 1;background: #fff;
    padding: 7px;
    border-radius: 50%;
    width: 35px;}
	button.close span{    color: red;}
	#collectionViewDiv{    width: 95%;
    margin: 0 auto;}
	.nav-mid{ padding: 8px;
    -webkit-box-shadow: 0px 0px 29px 0px rgb(0 0 0 / 33%);
    -moz-box-shadow: 0px 0px 29px 0px rgba(0,0,0,0.33);
    box-shadow: 0px 0px 29px 0px rgb(0 0 0 / 33%);
    border-radius: 50px; }
	.panel-body .nav-tabs{border: none;}
	.body-section p{    font-size: 11px;
    text-transform: uppercase;
    font-weight: bold;
    color: #8c8c8c;
    letter-spacing: 1px;
    margin-bottom: 3px;}
	.body-section h3{    margin-top: 0;
    font-weight: normal;
    font-size: 11px;    margin-bottom: 12px;
}
	.progress-circle .top-progress-circle{     color: #fff;
    text-transform: uppercase;
    font-size: 11px;
    background-image: linear-gradient(#3051d3, #14297b);
    padding: 12px 6px;
    border-radius: 10px;
    margin-top: 0;
    font-weight: normal;}
	.self-info h3{   margin-top: 0;
    font-weight: bold;
    font-size: 11px;    }
	.header-section{border-bottom: 3px solid #f7f7f7;
    display: flex;}
	.self-info{    padding-top: 22px;}
	.self-info h4{    font-weight: bold;
    color: #737373;font-size: 11px;}
	.right-button{    text-align: right;
    padding: 26px;}
	.right-button button, .right-button button:hover, .right-button button:focus, .right-button button:active{background: #2764ff;
    border: none;
    border-radius: 50px;
    padding: 10px 40px;    margin-left: 40px;}
	.body-section{    margin-top: 15px;}
	.panel-default>.panel-heading.custom-pd {
    padding: 15px;
}
	
.progress-container::before {
  content: "";
  background: #b0b0b0;
  position: absolute;
  top: 50%;
  left: 0;
  transform: translateY(-50%);
  height: 4px;
  width: 100%;
  z-index: 0;
}

.progress-container {
    display: flex;
    justify-content: space-between;
    position: relative;
    max-width: 100%;
    width: 95%;
    padding: 40px 0px;
    margin: 0 auto;
}
	.progress-circle{    padding-top: 35px;}
.progress {background: #04cf3d;
    position: absolute;
    top: 50%;
    left: -12px;
    transform: translateY(-50%);
    height: 4px;
    width: 0%;
    z-index: 0;
    transition: 0.4s ease;
}
.body-section .col-lg-3 {
    min-height: 70px;
}
.circle {
  background: #b0b0b0;
  color: #999;
  border-radius: 50%;
  height: 40px;
  width: 40px;
  display: block;
  align-items: center;
  justify-content: center;
  border: 3px solid #b0b0b0;
  transition: .4s ease;
	    z-index: 99;
	position: relative;
}

.circle.active {
  border-color: #04cf3d;
	    background: #04cf3d;    position: relative;
}
	.circle:hover{text-decoration: none;}
	.circle.active i{display: block !important;    font-size: 23px;
    color: white;    position: absolute;
    top: 6px;
    left: 6px;}
	.circle.inprogress{  border-color: #ff9000;
	    background: #ff9000;}
	.progress-bottom-border{     border-top: 3px solid #b0b0b0;
     min-height: 183px;}
	.body-section .email{text-transform: lowercase;}
	.progress-container .circle p{    font-weight: normal;
    color: #000;
    text-align: center;
    width: 97px;
    position: relative;
    top: 46px;
    left: -32px;
    text-transform: capitalize;
    font-size: 11px;}
	.d-flex{display: flex;}
	.img-sec img{    width: 50px;
    margin-right: 12px;}
	
	.circle-pro {
    position: relative;
    width: 150px;
    height: 150px;
    margin: 0.5rem;
    border-radius: 50%;
    background: #FFCDB2;
    overflow: hidden;
}
		
		.circle-pro .inner {
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 115px;
    height: 115px;
    background: #fff;
    border-radius: 50%;
    font-size: 1.85em;
    font-weight: 300;
    color: rgba(0, 0, 0, 1);
}
	*{    outline: none !important;}
	.col-lg-9.body-section{margin-top: 10px;
    border-right: 3px solid #b0b0b0;}
	.progress-circle p{    text-align: center;
    font-weight: bold;
    margin-top: 10px;
    font-size: 11px;}

	.modal-open .modal{    padding-top: 115px;}
	
	.upper-sec{    padding: 22px 10px;    padding-bottom: 14px;    overflow-x: hidden !important;padding-top: 10px !important;}
/*.upper-sec::-webkit-scrollbar {
  width: 20px;
}


.upper-sec::-webkit-scrollbar-track {
  box-shadow: inset 0 0 5px grey; 
  border-radius: 10px;
}
 

.upper-sec::-webkit-scrollbar-thumb {
 
  border-radius: 10px;
	    background-image: linear-gradient(#3051d3, #14297b);
}


.upper-sec::-webkit-scrollbar-thumb:hover {
     background-image: linear-gradient(#3051d3, #14297b);
}*/
	.form-group.inner-button{width: 48%; text-align: center;}
	.ripple {
  margin: auto;
  margin-top: 5rem;
  background-color: #fff;
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  display: grid;
  animation: ripple 3s linear infinite;
}

.ripple::before,
.ripple::after {
  content: "";
  grid-area: 1/1;
  border-radius: 50%;
  animation: inherit;
  animation-delay: 1s;
}
	.expand{      position: relative;
    left: -9px;
    top: -17px;
    color: #000;
    font-size: 32px;
    font-weight: bold;}
	.expand.down i{    transform: rotate(180deg);}
	.expand i{    background: #fff;
    border-radius: 50%;
    height: 30px;
    width: 30px;
    text-align: center;}
.ripple::after {
  animation-delay: 2s;
}
	.invisible-button{    position: absolute;
    background: white;
    z-index: 9999;
    top: 40px;
    padding: 10px 0;
    right: 13px;    text-align: right;}
	.invisible-button..invisible{display: none;}
@keyframes ripple {
  0% {
    box-shadow: 0 0 0 .7rem rgba(0, 0, 0, 0.2);
  }
  100% {
    box-shadow: 0 0 0 8rem rgba(0, 0, 0, 0);
  }
}
	.in-between-filter{    margin-bottom: 49px;    padding: 10px;
    background: #fff;
    border-radius: 10px;
    -webkit-box-shadow: 0 1px 1px rgb(0 0 0 / 5%);
    box-shadow: 0 1px 1px rgb(0 0 0 / 5%);    position: relative;}
	.in-between-filter p{ display: inline-block;
    margin: 0 10px;
    padding: 8px 21px;
    background: #ebecf0;
    border-radius: 6px;font-weight: bold; cursor: pointer;}
	
	.in-between-filter p:hover a{    text-decoration: line-through;    }
	
	.in-between-filter p span{     font-weight: bold;
    color: red;
    margin-left: 10px;
    background: #fff;
    padding: 2px 5px;
    border-radius: 50px;
    height: 30px;
    width: 30px;}
	.expand-design{    position: absolute;
    left: 49%;
    bottom: -10px;}
	


	@media only screen and (max-width: 600px) {
					.panel-body.filter-body{width: 100%;}
	.panel-body.filter-body .form-group{width: 100%;}

						.upload span {
    position: static;
}
				.filter-button a{    width: 100%;
    margin-top: 5px;    font-size: 11px;}
				.action-tool span{    width: 100%;
    margin-bottom: 5px;    margin-right: 0;}
				.modal-dialog {
    width: 95%;
}
				.profile-userpic img {
    width: 50%;
}
				.bio-row p span{vertical-align: text-top;}
				.bio-row{    width: 100%;}
				.left-container, .middle-container, .right-container{    width: 100%;}
			.progress-parent{    overflow: scroll;}
			.progress-bottom-border{    width: 1000px;}
			#requestProgressReport .panel-body{    width: 100%;}
			#requestProgressReport .panel-heading .col-lg-2 .close{    position: absolute;
    right: 0;
    top: -31px;}
			.self-info, #requestProgressReport .header-section{     width: 100%; display: block;}
			.right-button{    padding: 10px;
    width: 100%;}
			.right-button a{ width: 48%;}
		.not-completed a {
    width: 100% !important;
}
	}
	
	
</style>

<section class="filter-section">
<div class="container-fluid">
  <div class="row">
	<div class="col-lg-9">
	  <ul>
		<li><a href="javascript:void(0)" id="report-button" data-toggle="modal" data-target="#myModalDept">By Department</a></li>
	

		</ul>
	  </div>
	  
	  <div class="col-lg-3 text-right">
		  <ul>
	 
	  <!--<li><a href="javascript:void(0);" onclick="showAddPanel();"><i class="fa fa-plus" aria-hidden="true"></i> Request</a></li>-->
			  </ul>
	  </div>
	</div>
</div>
<script>

function showENBDReport()
{
	$("#loadingImage").fadeIn(1000);
	$('#addPanelMain').hide();
	$('#addPanelMain').html("");
	 $.ajax({
							type: "GET",
							url: "{{ url('jonusUploadAjax') }}",
							
							success: function(response){
								$("#loadingImage").hide();
								$('#addPanelMain').html(response);
								$('#addPanelMain').slideDown("1000");
								
								
							}
						});	
}

function closeJonus()
{
	$('#addPanelMain').slideUp(1000);
	$('#addPanelMain').html("");
}
</script>
	</section>
	

	<div id="mainPanel" class="graph-section" style="display:none;"></div>
	<div id="loadingImage"  class="loadingImage" style="display:none;"><img src="{{url('/hrm/images/loader-1.gif')}}" alt="Image" width="10%"/></div>

<div class="container-fluid">
<div class="row">
<div class="col-lg-12">
<div class="panel panel-default">
	
	<div class="panel-body filter-body">
	<?php $empId=Session::get('EmployeeId');?>						

	<ul class="nav nav-tabs">
	<?php if($empId== 96 || $empId== 97){ ?>
		<li class="active" style="padding-right: 3px;"><a data-toggle="tab" href="#All" class="inner-button">All</a></li>
		<li class="padding-right: 3px;" style="padding-right: 3px;"><a data-toggle="tab" href="#ENBD" class="inner-button">ENBD</a></li>
	<?php }
	else if($empId== 94 || $empId== 95){ ?>
		<li class="active" style="padding-right: 3px;"><a data-toggle="tab" href="#All" class="inner-button">All</a></li>
		<li class="padding-right: 3px;" style="padding-right: 3px;"><a data-toggle="tab" href="#deem"  id ="documenttab" class="inner-button">Deem</a></li>
		<li style="padding-right: 3px;"><a data-toggle="tab" href="#mashreq" id="companytab" class="inner-button">Mashreq</a></li>
		<li style="padding-right: 3px;"><a data-toggle="tab" href="#aafaq" id="aafaqtab" class="inner-button">Aafaq</a></li>
	<?php }
	else{ ?>
		<li class="active" style="padding-right: 3px;"><a data-toggle="tab" href="#All" class="inner-button">All</a></li>
		<li class="padding-right: 3px;" style="padding-right: 3px;"><a data-toggle="tab" href="#ENBD" class="inner-button">ENBD</a></li>
		<li style="padding-right: 3px;"><a data-toggle="tab" href="#deem"  id ="documenttab" class="inner-button">Deem</a></li>
		<li style="padding-right: 3px;"><a data-toggle="tab" href="#mashreq" id="companytab" class="inner-button">Mashreq</a></li>
		<li style="padding-right: 3px;"><a data-toggle="tab" href="#aafaq" id="aafaqtab" class="inner-button">Aafaq</a></li>
	<?php }?>
	  </ul>
	  <div class="tab" role="tabpanel" style="width:100%;">
 <div class="tab-content">
 
 <div id="All" class="tab-pane fade in active">
 <div class="panel-upper">
<div class="panel panel-default" >
  <div class="panel-heading">
    <div class="col-lg-6">All Document Collection List (On-boarding)</div>
       <div class="col-lg-6 text-right">Total No. of Entry - <span id="updateCountall"></span></div>
    
  </div>
  <div class="panel-body"> 


       
          <div class="row">
            <div class="col-sm-12" id="listingPanelall">		
				
            </div>
          </div>
          
</div>
  </div>
  </div>
  </div>
  
 <div id="ENBD" class="tab-pane fade in">
 <div class="panel-upper">
<div class="panel panel-default" >
  <div class="panel-heading">
    <div class="col-lg-6">ENBD Document Collection List (On-boarding)</div>
       <div class="col-lg-6 text-right">Total No. of Entry - <span id="updateCountenbd"></span></div>
    
  </div>
  <div class="panel-body"> 


       
          <div class="row">
            <div class="col-sm-12" id="listingPanelenbd">		
				
            </div>
          </div>
          
</div>
  </div>
  </div>
  </div>
  <div id="deem" class="tab-pane fade in <?php if($empId== 94 || $empId== 95){echo "active"; } else{ } ?>">
  <div class="panel-upper">
<div class="panel panel-default" >
  <div class="panel-heading">
    <div class="col-lg-6">Deem Document Collection List (On-boarding)</div>
       <div class="col-lg-6 text-right">Total No. of Entry - <span id="updateCountdeem"></span></div>
    
  </div>
  <div class="panel-body"> 


       
          <div class="row">
            <div class="col-sm-12" id="listingPaneldeem">		
				
            </div>
          </div>
          

  </div>
  </div>
  </div>
  </div>
   <div id="mashreq" class="tab-pane fade">
   <div class="panel-upper">
	<div class="panel panel-default" >
  <div class="panel-heading">
    <div class="col-lg-6">Mashreq Document Collection List (On-boarding)</div>
       <div class="col-lg-6 text-right">Total No. of Entry - <span id="updateCountmashreq"></span></div>
    
  </div>
  <div class="panel-body"> 
    
          <div class="row">
            <div class="col-sm-12" id="listingPanelmashreq">		
				
            </div>
          </div>
          

  </div>
  </div>
  
  </div>
  
  
  
</div>

   <div id="aafaq" class="tab-pane fade">
   <div class="panel-upper">
	<div class="panel panel-default" >
  <div class="panel-heading">
    <div class="col-lg-6">Aafaq Document Collection List (On-boarding)</div>
       <div class="col-lg-6 text-right">Total No. of Entry - <span id="updateCountaafaq"></span></div>
    
  </div>
  <div class="panel-body"> 
    
          <div class="row">
            <div class="col-sm-12" id="listingPanelaafaq">		
				
            </div>
          </div>
          

  </div>
  </div>
  
  </div>
  
  
  
</div>
			</div>
		</div>
		

	</div>
	
	</div>
	</div>
	
	
<!--- end data tab------>	
<div id="myModalDept" class="modal fade" role="dialog">
  <div class="modal-dialog"> 
    
    <!-- Modal content-->
    <div class="modal-content">
      <div class="panel panel-default report">
        <div class="panel-heading">
<div class="col-lg-8">Filter By Department</div>
          <div class="col-lg-4">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
        </div>
        <div class="panel-body" > 
		
		<div class="row">
<ul class="pop-button">
@foreach($departmentIdArray as $departid=>$departName)
<li><a href="javascript:void(0)" class="inner-button" onclick="filterReportAsPerDepartmentr({{$departid}})">{{$departName}}</a></li>
@endforeach
		
		

		</ul>
		</div>
			  <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
      </div>
		</div>
      </div>
		
		
    </div>
  </div>
  
</div>

<script>
	function filterReportAsPerDepartmentr(val)
	{
		 $.ajax({
							type: "GET",
							url: "{{ url('filterReportAsPerDepartmentr') }}/"+val,
							
							success: function(response){
							
								$('#myModalDept').modal("hide");	
								resetlisting();	
								
								
							}
						});	
	}
	
	function updateFilter()
	{
		$.ajax({
							type: "GET",
							url: "{{ url('updateFilterOnBoarding') }}/",
							
							success: function(response){
								if(parseInt(response) != parseInt(1))
								{
								$("#filtersSet").html(response);
								$("#filterSetPanel").fadeIn(1000);
								}
								
							}
						});	
	}
	
	function cancelFilters(type)
	{
		$.ajax({
							type: "GET",
							url: "{{ url('cancelFiltersOnboard') }}/"+type,
							
							success: function(response){
							    	$("#filtersSet").html('');
								$("#filterSetPanel").hide();
								resetlisting();	
								
								
							}
						});	
	}
	
	
</script>
<script>
  function detailsMISReport(misid)
  {
	  $.ajax({
							type: "GET",
							url: "{{ url('detailsMISReport') }}/"+misid,
							
							success: function(response){
									
								$("#documentDetailsPanel").html(response);
								$("#myModal").modal("show");
								
							}
						});	
  }
  
  $jenbdCards(document).ready(function(){

	  $("#loadingImage").fadeIn(1000);
	setTimeout(listingFirstTimeAll, 1000);
	setTimeout(listingFirstTimeenbd, 1000);
	setTimeout(listingFirstTimedeem, 1000);
	setTimeout(listingFirstTimemashreq, 1000);
	setTimeout(listingFirstTimeaafaq, 1000);
  });
  
  function listingFirstTimeaafaq()
  {
	  $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingAafaq') }}",
							
							success: function(response){
									
								$("#listingPanelaafaq").html(response);
								$jenbdCards('#cnameAafaq').selectpicker('refresh');
								$jenbdCards('#cemailAafaq').selectpicker('refresh');
								$jenbdCards('#designationAafaq').selectpicker('refresh');
								$jenbdCards('#departmentAafaq').selectpicker('refresh');
								$jenbdCards('#openingAafaq').selectpicker('refresh');
								$jenbdCards('#statusAafaq').selectpicker('refresh');
								$jenbdCards('#vintageAafaq').selectpicker('refresh');
								$jenbdCards('#companyAafaq').selectpicker('refresh');
								
								
								
								 $("#loadingImage").hide();
								 updateFilter();
								
							}
						});	
  }
  
  function resetlistingenbd()
  {
	   $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingAafaq') }}/",
							
							success: function(response){
									
								$("#listingPanelaafaq").html(response);
								$jenbdCards('#cnameAafaq').selectpicker('refresh');
								$jenbdCards('#cemailAafaq').selectpicker('refresh');
								$jenbdCards('#designationAafaq').selectpicker('refresh');
								$jenbdCards('#departmentAafaq').selectpicker('refresh');
								$jenbdCards('#openingAafaq').selectpicker('refresh');
								$jenbdCards('#statusAafaq').selectpicker('refresh');
								$jenbdCards('#vintageAafaq').selectpicker('refresh');
								$jenbdCards('#companyAafaq').selectpicker('refresh');
								$("#loadingImage").hide();
								updateFilter();
							}
						});	
  }
  function listingFirstTimeAll()
  {
	  
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingAll') }}",
							
							success: function(response){
									
								$("#listingPanelall").html(response);
								$jenbdCards('#cnameAll').selectpicker('refresh');
								$jenbdCards('#cemailAll').selectpicker('refresh');
								$jenbdCards('#designationAll').selectpicker('refresh');
								$jenbdCards('#departmentAll').selectpicker('refresh');
								$jenbdCards('#openingAll').selectpicker('refresh');
								$jenbdCards('#statusAll').selectpicker('refresh');
								$jenbdCards('#vintageAll').selectpicker('refresh');
								$jenbdCards('#companyAll').selectpicker('refresh');
								
								
								
								 $("#loadingImage").hide();
								 updateFilter();
								
							}
						});	
  }
  
  function listingFirstTimeenbd()
  {
	  
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingENBD') }}",
							
							success: function(response){
									
								$("#listingPanelenbd").html(response);
								$jenbdCards('#cname').selectpicker('refresh');
								$jenbdCards('#cemail').selectpicker('refresh');
								$jenbdCards('#designation').selectpicker('refresh');
								$jenbdCards('#department').selectpicker('refresh');
								$jenbdCards('#opening').selectpicker('refresh');
								$jenbdCards('#status').selectpicker('refresh');
								$jenbdCards('#vintage').selectpicker('refresh');
								$jenbdCards('#company').selectpicker('refresh');
								
								
								
								 $("#loadingImage").hide();
								 updateFilter();
								
							}
						});	
  }
  function resetlistingenbd()
  {
	  // $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingENBD') }}/",
							
							success: function(response){
									
								$("#listingPanelenbd").html(response);
								$jenbdCards('#cname').selectpicker('refresh');
								$jenbdCards('#cemail').selectpicker('refresh');
								$jenbdCards('#designation').selectpicker('refresh');
								$jenbdCards('#department').selectpicker('refresh');
								$jenbdCards('#opening').selectpicker('refresh');
								$jenbdCards('#status').selectpicker('refresh');
								$jenbdCards('#vintage').selectpicker('refresh');
								$jenbdCards('#company').selectpicker('refresh');
								$("#loadingImage").hide();
								updateFilter();
							}
						});	
  }
  
    function resetlistingall()
  {
	   //$("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingAll') }}/",
							
							success: function(response){
									
								$("#listingPanelall").html(response);
								$jenbdCards('#cnameAll').selectpicker('refresh');
								$jenbdCards('#cemailAll').selectpicker('refresh');
								$jenbdCards('#designationAll').selectpicker('refresh');
								$jenbdCards('#departmentAll').selectpicker('refresh');
								$jenbdCards('#openingAll').selectpicker('refresh');
								$jenbdCards('#statusAll').selectpicker('refresh');
								$jenbdCards('#vintageAll').selectpicker('refresh');
								$jenbdCards('#companyAll').selectpicker('refresh');
								$("#loadingImage").hide();
								updateFilter();
							}
						});	
  }
  
   function resetlistingaafaq()
  {
	   //$("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingAafaq') }}/",
							
							success: function(response){
									
								$("#resetlistingaafaq").html(response);
								$jenbdCards('#cnameAafaq').selectpicker('refresh');
								$jenbdCards('#cemailAafaq').selectpicker('refresh');
								$jenbdCards('#designationAafaq').selectpicker('refresh');
								$jenbdCards('#departmentAafaq').selectpicker('refresh');
								$jenbdCards('#openingAafaq').selectpicker('refresh');
								$jenbdCards('#statusAafaq').selectpicker('refresh');
								$jenbdCards('#vintageAafaq').selectpicker('refresh');
								$jenbdCards('#companyAafaq').selectpicker('refresh');
								$("#loadingImage").hide();
								updateFilter();
							}
						});	
  }
  
  function runpageAjaxall(pageUrl)
  {
	  //alert(pageUrl);
	   $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingAll') }}"+pageUrl,
							
							success: function(response){
									
								$("#listingPanelall").html(response);
								$jenbdCards('#cnameAll').selectpicker('refresh');
								$jenbdCards('#cemailAll').selectpicker('refresh');
								$jenbdCards('#designationAll').selectpicker('refresh');
								$jenbdCards('#departmentAll').selectpicker('refresh');
								$jenbdCards('#openingAll').selectpicker('refresh');
								$jenbdCards('#statusAll').selectpicker('refresh');
								$jenbdCards('#vintageAll').selectpicker('refresh');
								$jenbdCards('#companyAll').selectpicker('refresh');
								$("#loadingImage").hide();
							}
						});	
  }
  
  function runpageAjaxaafaq(pageUrl)
  {
	  //alert(pageUrl);
	   $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingAafaq') }}"+pageUrl,
							
							success: function(response){
									
								$("#listingPanelaafaq").html(response);
								$jenbdCards('#cnameAafaq').selectpicker('refresh');
								$jenbdCards('#cemailAafaq').selectpicker('refresh');
								$jenbdCards('#designationAafaq').selectpicker('refresh');
								$jenbdCards('#departmentAafaq').selectpicker('refresh');
								$jenbdCards('#openingAafaq').selectpicker('refresh');
								$jenbdCards('#statusAafaq').selectpicker('refresh');
								$jenbdCards('#vintageAafaq').selectpicker('refresh');
								$jenbdCards('#companyAafaq').selectpicker('refresh');
								$("#loadingImage").hide();
							}
						});	
  }
  
  
  function runpageAjaxenbd(pageUrl)
  {
	  //alert(pageUrl);
	   $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingENBD') }}"+pageUrl,
							
							success: function(response){
									
								$("#listingPanelenbd").html(response);
								$jenbdCards('#cname').selectpicker('refresh');
								$jenbdCards('#cemail').selectpicker('refresh');
								$jenbdCards('#designation').selectpicker('refresh');
								$jenbdCards('#department').selectpicker('refresh');
								$jenbdCards('#opening').selectpicker('refresh');
								$jenbdCards('#status').selectpicker('refresh');
								$jenbdCards('#vintage').selectpicker('refresh');
								$jenbdCards('#company').selectpicker('refresh');
								$("#loadingImage").hide();
							}
						});	
  }
  function listingFirstTimedeem()
  {
	  
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingdeem') }}",
							
							success: function(response){
									
								$("#listingPaneldeem").html(response);
								$jenbdCards('#cnamedeem').selectpicker('refresh');
								$jenbdCards('#cemaildeem').selectpicker('refresh');
								$jenbdCards('#designationdeem').selectpicker('refresh');
								$jenbdCards('#departmentdeem').selectpicker('refresh');
								$jenbdCards('#openingdeem').selectpicker('refresh');
								$jenbdCards('#statusdeem').selectpicker('refresh');
								$jenbdCards('#vintagedeem').selectpicker('refresh');
								$jenbdCards('#companydeem').selectpicker('refresh');
								
								
								
								 $("#loadingImage").hide();
								 updateFilter();
								
							}
						});	
  }
  function resetlistingdeem()
  {
	   //$("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingdeem') }}/",
							
							success: function(response){
									
								$("#listingPaneldeem").html(response);
								$jenbdCards('#cnamedeem').selectpicker('refresh');
								$jenbdCards('#cemaildeem').selectpicker('refresh');
								$jenbdCards('#designationdeem').selectpicker('refresh');
								$jenbdCards('#departmentdeem').selectpicker('refresh');
								$jenbdCards('#openingdeem').selectpicker('refresh');
								$jenbdCards('#statusdeem').selectpicker('refresh');
								$jenbdCards('#vintagedeem').selectpicker('refresh');
								$jenbdCards('#companydeem').selectpicker('refresh');
								$("#loadingImage").hide();
								updateFilter();
							}
						});	
  }
  function runpageAjaxdeem(pageUrl)
  {
	   $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingdeem') }}"+pageUrl,
							
							success: function(response){
									
								$("#listingPaneldeem").html(response);
								$jenbdCards('#cnamedeem').selectpicker('refresh');
								$jenbdCards('#cemaildeem').selectpicker('refresh');
								$jenbdCards('#designationdeem').selectpicker('refresh');
								$jenbdCards('#departmentdeem').selectpicker('refresh');
								$jenbdCards('#openingdeem').selectpicker('refresh');
								$jenbdCards('#statusdeem').selectpicker('refresh');
								$jenbdCards('#vintagedeem').selectpicker('refresh');
								$jenbdCards('#companydeem').selectpicker('refresh');
								$("#loadingImage").hide();
							}
						});	
  }
   function listingFirstTimemashreq()
  {
	  
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingmashreq') }}",
							
							success: function(response){
									
								$("#listingPanelmashreq").html(response);
								$jenbdCards('#cnamemashreq').selectpicker('refresh');
								$jenbdCards('#cemailmashreq').selectpicker('refresh');
								$jenbdCards('#designationmashreq').selectpicker('refresh');
								$jenbdCards('#departmentmashreq').selectpicker('refresh');
								$jenbdCards('#openingmashreq').selectpicker('refresh');
								$jenbdCards('#statusmashreq').selectpicker('refresh');
								$jenbdCards('#vintagemashreq').selectpicker('refresh');
								$jenbdCards('#companymashreq').selectpicker('refresh');
								
								
								
								 $("#loadingImage").hide();
								 updateFilter();
								
							}
						});	
  }
  function resetlistingmashreq()
  {
	  // $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingmashreq') }}/",
							
							success: function(response){
									
								$("#listingPanelmashreq").html(response);
								$jenbdCards('#cnamemashreq').selectpicker('refresh');
								$jenbdCards('#cemailmashreq').selectpicker('refresh');
								$jenbdCards('#designationmashreq').selectpicker('refresh');
								$jenbdCards('#departmentmashreq').selectpicker('refresh');
								$jenbdCards('#openingmashreq').selectpicker('refresh');
								$jenbdCards('#statusmashreq').selectpicker('refresh');
								$jenbdCards('#vintagemashreq').selectpicker('refresh');
								$jenbdCards('#companymashreq').selectpicker('refresh');
								$("#loadingImage").hide();
								updateFilter();
							}
						});	
  }
  function runpageAjaxmashreq(pageUrl)
  {
	   $("#loadingImage").fadeIn(1000);
	   $.ajax({
							type: "GET",
							url: "{{ url('listingPageonboardingmashreq') }}"+pageUrl,
							
							success: function(response){
									
								$("#listingPanelmashreq").html(response);
								$jenbdCards('#cnamemashreq').selectpicker('refresh');
								$jenbdCards('#cemailmashreq').selectpicker('refresh');
								$jenbdCards('#designationmashreq').selectpicker('refresh');
								$jenbdCards('#departmentmashreq').selectpicker('refresh');
								$jenbdCards('#openingmashreq').selectpicker('refresh');
								$jenbdCards('#statusmashreq').selectpicker('refresh');
								$jenbdCards('#vintagemashreq').selectpicker('refresh');
								$jenbdCards('#companymashreq').selectpicker('refresh');
								$("#loadingImage").hide();
							}
						});	
  }
  
</script>


<script>
	
$jenbdCards(document).ready(function(){
  $jenbdCards("#accord1").click(function(){
    $jenbdCards(".top-accord").toggleClass("show-accord");
  });
});

function setOffSetOnboarding()
{
	var c = $jenbdCards("#offsetV").val();
	//window.location.href="{{url('setOffSetForENDBCardsInnerMIS')}}/"+c;
	$.ajax({
						type: "GET",
							url: "{{ url('setOffSetForOnboarding') }}/"+c,
																	
								success: function(response){
																						
									resetlistingmashreq();										
									resetlistingdeem();										
									resetlistingall();										
									resetlistingenbd();										
									resetlistingaafaq();										
								}
						});
}

function applyFilter()
{
	document.getElementById("enbdCardsFilters").submit();
}

function resetFilter()
{
	window.location.href="{{url('resetEnbdCardsMISFilter')}}";
}

function showAddPanel()
{
	$("#loadingImage").fadeIn(1000);
	$.ajax({
						type: "GET",
							url: "{{ url('addDocumentCollectionAjax') }}",
																	
								success: function(response){
										$("#loadingImage").hide();								
										$("#mainPanel").html(response);		
										$("#mainPanel").slideDown(1000);
										$jenbdCards('#hiringSource').selectpicker('refresh');
										$jenbdCards('#recruiterName').selectpicker('refresh');										
										$jenbdCards('#job_opening').selectpicker('refresh');										
										$jenbdCards('#department').selectpicker('refresh');										
																				
																					
								}
						});
}

function filterByCandidateNameAll(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateNameAll') }}/"+$("#cnameAll").val(),
													
				success: function(response){
						resetlistingall();												
																	
				}
		});
}
function filterByVintageAll(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByVintageAll') }}/"+$("#vintageAll").val(),
													
				success: function(response){
						resetlistingall();												
																	
				}
		});
}
function filterByStatusAll(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByStatussAll') }}/"+$("#statusAll").val(),
													
				success: function(response){
						resetlistingall();												
																	
				}
		});
}

function filterByOpeningAll(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByOpeningAll') }}/"+$("#openingAll").val(),
													
				success: function(response){
						resetlistingall();												
																	
				}
		});
}



function filterByCandidateNameAafaq(){
	$("#listingPanelaafaq").html('');
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateNameAafaq') }}/"+$("#cnameAafaq").val(),
													
				success: function(response){
						resetlistingaafaq();												
																	
				}
		});
}
function filterByVintageAafaq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByVintageAafaq') }}/"+$("#vintageAafaq").val(),
													
				success: function(response){
						resetlistingaafaq();												
																	
				}
		});
}
function filterByStatusAafaq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByStatussAafaq') }}/"+$("#statusAafaq").val(),
													
				success: function(response){
						resetlistingaafaq();												
																	
				}
		});
}

function filterByOpeningAafaq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByOpeningAafaq') }}/"+$("#openingAafaq").val(),
													
				success: function(response){
						resetlistingaafaq();												
																	
				}
		});
}



function filterByCandidateName(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateName') }}/"+$("#cname").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}
function filterByVintage(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByVintage') }}/"+$("#vintage").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}
function filterByCandidateEmail(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateEmail') }}/"+$("#cemail").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}
function filterByDesignation(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByDesignation') }}/"+$("#designation").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}
function filterByDepartment(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByDepartment') }}/"+$("#department").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}
function filterByOpening(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByOpening') }}/"+$("#opening").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}
function filterByCompany(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCompany') }}/"+$("#company").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}
function filterByStatus(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByStatuss') }}/"+$("#status").val(),
													
				success: function(response){
						resetlistingenbd();												
																	
				}
		});
}

//start Deem

function filterByCandidateNameDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateNameDeem') }}/"+$("#cnamedeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}
function filterByVintageDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByVintageDeem') }}/"+$("#vintagedeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}
function filterByCandidateEmailDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateEmailDeem') }}/"+$("#cemaildeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}
function filterByDesignationDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByDesignationDeem') }}/"+$("#designationdeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}
function filterByDepartmentDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByDepartmentDeem') }}/"+$("#departmentdeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}
function filterByOpeningDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByOpeningDeem') }}/"+$("#openingdeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}
function filterByCompanyDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCompanyDeem') }}/"+$("#companydeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}
function filterByStatusDeem(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByStatussDeem') }}/"+$("#statusdeem").val(),
													
				success: function(response){
						resetlistingdeem();												
																	
				}
		});
}

//Start masr
function filterByCandidateNamemashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateNamemashreq') }}/"+$("#cnamemashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
function filterByVintagemashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByVintagemashreq') }}/"+$("#vintagemashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
function filterByCandidateEmailmashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCandidateEmailmashreq') }}/"+$("#cemailmashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
function filterByDesignationmashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByDesignationmashreq') }}/"+$("#designationmashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
function filterByDepartmentmashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByDepartmentmashreq') }}/"+$("#departmentmashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
function filterByOpeningmashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByOpeningmashreq') }}/"+$("#openingmashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
function filterByCompanymashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByCompanymashreq') }}/"+$("#companymashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
function filterByStatusmashreq(){
$.ajax({
		type: "GET",
			url: "{{ url('filterByStatusmashreq') }}/"+$("#statusmashreq").val(),
													
				success: function(response){
						resetlistingmashreq();												
																	
				}
		});
}
</script>
<script>
 $jenbdCards(document).ready(function(){
	  //$("#loadingImage").fadeIn(1000);
	 $.ajax({
			type: "GET",
			url: "{{ url('updateVintage')}}",
			
			success: function(response){
					
				$("#listingPanel").html(response);
				
			}
		});	
  });
</script>

@stop