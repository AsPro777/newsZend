<?php
return <<<S
<div class="form-group row"> <span class="control-label col-lg-4">Должность</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-dolzhn form-control my-control" value="" name="dolzhn" maxlength="100">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Фамилия</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-f form-control my-control" value="" name="f" maxlength="100">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Имя</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-i form-control my-control" value="" name="i" maxlength="100">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Отчество</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-o form-control my-control" value="" name="o" maxlength="100">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Пол</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<select class="cls-sex form-control my-control" value="" name="sex">
				<option value="0">Женский</option>
				<option value="1">Мужской</option>
			</select>
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Дата рождения</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-dr form-control my-control" value="" name="dr" maxlength="10">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Гражданство</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<select class="cls-grazhd form-control my-control" value="" name="grazhd"></select>
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Удостоверение личности</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<select class="cls-passport-type form-control my-control" value="" name="passport_type"></select>
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Серия и номер</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-passport form-control my-control" value="" maxlength="11" name="passport">
			<div id="doc-num-example"></div>
		</div>
	</div>
</div>
S;
