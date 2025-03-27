

@if($keywordId == 1)
	
<div class="row">
	<div class="col-lg-12 d-flex">
<p>RMs with</p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> 
	

		<p>than</p> <input type="text" size="2" name="x_value" id="x_value" required> <p>number of <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select> <p>in last</p><input type="text" size="2" name="y_value" id="y_value" required> <p> days.</p>
	</div>
	</div>
@elseif($keywordId == 2)
<div class="row">
	<div class="col-lg-12 d-flex">
	<p>Approval rate</p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select>  <p>than</p> <input type="text" size="2" name="x_value" id="x_value" required> <p>%</p>.
	</div>
		</div>
@elseif($keywordId == 3)
<div class="row">
		<div class="col-lg-12 d-flex">
				<p>RMs with<p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> than <input type="text" size="2" name="x_value" id="x_value" required> <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select><p> as of today.</p>
		</div>
	</div>
@elseif($keywordId == 4)
<div class="row">
				<div class="col-lg-12 d-flex">
				
						RMs with bookings less than, <input type="text" size="2" name="x_value" id="x_value" required>% of target as of today
				</div>
	</div>
@elseif($keywordId == 5)
<div class="row">
				<div class="col-lg-12 d-flex">
				
						<p>RMs with</p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than</p> <input type="text" size="2" name="x_value" id="x_value" required> <p>%</p> <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select><p> in customer with</p> <select name="more_less1" id="more_less1" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than salary of AED</p> <input type="text" size="2" name="xyz_value" id="xyz_value" required><p>.</p>
				</div>
	</div>
@elseif($keywordId == 6)
<div class="row">
				<div class="col-lg-12 d-flex">
				
						<p>RMs with</p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than</p> <input type="text" size="2" name="x_value" id="x_value" required> <p>%</p> <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select> <p>in</p> <select name="card_type[]" id="card_type" class="form-control selectpicker" title="Select Card Type" multiple required>
		
	@foreach($collectionCardType as $cardT)
			
		@if($cardT->card_type != '')
			<option value="{{$cardT->card_type}}">{{$cardT->card_type}}</option>
		@endif
	@endforeach
</select><p>.</p>
				</div>
	</div>
	@elseif($keywordId == 7)
<div class="row">
				<div class="col-lg-12 d-flex">
				
						<p>RMs with</p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than</p> <input type="text" size="2" name="x_value" id="x_value" required> <p>%</p> <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select> <p>in customer with bureau score</p> <select name="more_less1" id="more_less1" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than</p> <input type="text" size="2" name="xyz_value" id="xyz_value" required><p>.</p>
				</div>
	</div>
	@elseif($keywordId == 8)
<div class="row">
				<div class="col-lg-12 d-flex">
				
						<p>RMs with</p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than</p> <input type="text" size="2" name="x_value" id="x_value" required> <p>%</p> <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select> <p>in customer with MR score</p> <select name="more_less1" id="more_less1" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than</p> <input type="text" size="2" name="xyz_value" id="xyz_value" required><p>.</p>
				</div>
	</div>
	@elseif($keywordId == 9)
<div class="row">
				<div class="col-lg-12 d-flex">
				
						<p>RMs with</p> <select name="more_less" id="more_less" required>
  <option value=""></option>
  <option value="more">More</option>
  <option value="less">Less</option>
</select> <p>than</p> <input type="text" size="2" name="x_value" id="x_value" required> <p>%</p> <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select> <p>in customer with</p> <select name="bureau_segmentation[]" id="bureau_segmentation" class="form-control selectpicker" title="Select Bureau Segmentation" multiple required>

@foreach($bureauSegmentationArray as $bArray)
	@if($bArray->bureau_segmentation != '')
		<option value="{{$bArray->bureau_segmentation}}">{{$bArray->bureau_segmentation}}</option>
	@endif
@endforeach
</select><p>.</p>
				</div>
	</div>
@elseif($keywordId == 10)
<div class="row">
		<div class="col-lg-12 d-flex">
				<select name="best_worse" id="best_worse" required>
  <option value=""></option>
  <option value="best">Best</option>
  <option value="worse">Worse</option>
</select> agent
 <select name="submission_type" id="submission_type" required>
  <option value=""></option>
  <option value="submissions">submissions</option>
  <option value="bookings">bookings</option>
</select><p> as of today.</p>
		</div>
	</div>
	
@else
	

@endif

<input type="hidden" id="keywordIdValue" name="keywordIdValue" value="{{$keywordId}}"/>