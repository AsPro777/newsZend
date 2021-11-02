<?php
return <<<S
<div class="row">

	<div class="col-md-4" id="field-from-points">
		<label for="from-points">Отправление</label>
		<br>
		<input name="from-points" class="form-control" placeholder="Введите наименование">
	</div>

	<div class="col-md-4" id="field-trace-points">
		<label for="trace-points">Промежуточные пункты</label>
		<br>
		<input name="trace-points" class="form-control" placeholder="Введите наименование нового города">
	</div>

	<div class="col-md-4" id="field-to-points">
		<label for="to-points">Прибытие</label>
		<br>
		<input name="to-points" class="form-control" placeholder="Введите наименование">
	</div>
</div>

S;
