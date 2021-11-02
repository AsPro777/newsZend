<?php
return <<<S
<style>
#modal-reserved-pax-edit .form-group {
    margin-bottom: 5px !important;
}
</style>
<div class="form-group row"> <span class="control-label col-lg-4">Фамилия</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-f form-control my-control" name="f" maxlength="100" value="{$f}">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Имя</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-i form-control my-control" name="i" maxlength="100" value="{$i}">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Отчество</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-o form-control my-control" name="o" maxlength="100" value="{$o}">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Пол</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<select class="cls-sex form-control my-control" name="sex">
				<option value="0" {$selected_0}>Женский</option>
				<option value="1" {$selected_1}>Мужской</option>
			</select>
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Дата рождения</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-dr form-control my-control" name="dr" maxlength="10" value="{$dr}">
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Гражданство</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<select class="cls-grazhd form-control my-control" name="grazhd" data-init-id="{$grazhd_data_init_id}" data-init-val="{$grazhd_data_init_val}"></select>
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Удостоверение личности</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<select class="cls-passport-type form-control my-control" name="passport_type"  data-init-id="{$passport_type_data_init_id}" data-init-val="{$passport_type_data_init_val}"></select>
		</div>
	</div>
</div>
<div class="form-group row"> <span class="control-label col-lg-4">Серия и номер</span>
	<div class="col-lg-8">
		<div class="input-group" style="width: 100%;">
			<input class="cls-passport form-control my-control" maxlength="11" name="passport" data-mask="{$passport_type_mask}" value="{$doc_num}">
			<div id="doc-num-example">{$passport_type_help}</div>
		</div>
	</div>
</div>
S;
