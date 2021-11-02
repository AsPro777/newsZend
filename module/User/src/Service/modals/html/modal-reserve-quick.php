<?php
$dealers_style =<<<DD
display:none;
DD;

if(!empty($reseller)) $dealers_style = "";

return <<<S
<div class="row ticket-sell-content">
	<div class="col-md-8">
		<div class="panel">
			<div class="panel-heading">Билет</div>
			<div class="panel-body">
				<div class="form-group"> <span class="control-label col-xs-4">Пункт отправления</span>
					<div class="col-xs-8">
						<div class="input-group">
							<select class="cls-from-point remotable form-control my-control" value="" name="from-point"></select>
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Пункт прибытия</span>
					<div class="col-xs-8">
						<div class="input-group">
							<select class="cls-to-point remotable form-control my-control" name="to-point"></select>
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Тариф</span>
					<div class="col-xs-8">
						<div class="input-group">
							<select class="cls-tarif remotable form-control my-control" value="" name="tarif"></select>
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Мест багажа</span>
					<div class="col-xs-8">
						<div class="input-group">
							<select class="cls-cargo form-control my-control" value="" maxlength="1" name="cargo">
                                                            <option value="0">0</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                            <option value="6">6</option>
                                                            <option value="7">7</option>
                                                            <option value="8">8</option>
                                                            <option value="9">9</option>
                                                            <option value="10">10</option>
                                                        </select>
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Номер места</span>
					<div class="col-xs-8">
						<div class="input-group">
							<input class="cls-place form-control my-control" value="" maxlength="2" name="place" placeholder="0" readonly>
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Дата и время выкупа</span>
					<div class="col-xs-4">
						<div class="input-group">
                                                    <input class="cls-dt form-control my-control" value="$dt_drop" name="dt-drop">
						</div>
					</div>
					<div class="col-xs-4">
						<div class="input-group">
                                                    <input class="cls-tm form-control my-control" value="$tm_drop" name="tm-drop">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="panel">
			<div class="panel-heading">
                            Пассажир
                                <div style="display:inline-block;float:right;margin-right:0;width:342px;margin-left:10px;">
                                    <input class="form-control my-control cls-search-by" value="" maxlength="15" placeholder="Поиск по № телефона или документа">
                                </div>
                        </div>
			<div class="panel-body">
				<div class="form-group"> <span class="control-label col-xs-4">Телефон</span>
					<div class="col-xs-8">
						<div class="input-group">
							<input class="form-control cls-phone my-control" value="" maxlength="15" name="phone" placeholder="7-910-123-45-67">
							<div class="input-group-btn form-group">
								<button type="button" class="btn bg-teal btn-phone"><i class="icon-search4"></i></button>
								<div class="col-xs-8">
									<div class="input-group" style="width: 100%;"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Фамилия</span>
					<div class="col-xs-8">
						<div class="input-group">
							<input class="cls-f form-control my-control" value="" name="f" maxlength="100">
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Имя</span>
					<div class="col-xs-8">
						<div class="input-group">
							<input class="cls-i form-control my-control" value="" name="i" maxlength="100">
						</div>
					</div>
				</div>
				<div class="form-group"> <span class="control-label col-xs-4">Отчество</span>
					<div class="col-xs-8">
						<div class="input-group">
							<input class="cls-o form-control my-control" value="" name="o" maxlength="100">
						</div>
					</div>
				</div>

                                <div class="form-group">
                                    <span class="control-label col-xs-12">Комментарий к билету</span><br>
                                    <div class="col-xs-12">
					<div class="input-group">
                                            <textarea class="cls-comment form-control my-control" name="comment" placeholder="При необходимости введите сюда комментарии к билету" style="min-height: 65px;" />
                                        </div>
                                    </div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel">
			<div class="panel-heading">Места</div>
			<div class="panel-body">
				<div class="raw-places"></div>
				<div class="schema-places"></div>
			</div>
		</div>
		<div class="panel">
			<div class="panel-heading">Стоимость</div>
			<div class="panel-body">
				<div class="row">
                                        <span class="col-xs-8 control-label">Цена</span>
					<div  class="col-xs-4">
                                            <span  class="cls-ticket-cost-info"> </span>
                                            <input class="cls-ticket-cost-info" value="" name="ticket-cost-info" type=hidden>
					</div>
				</div>
				<div class="row">
                                        <span class="col-xs-8 control-label">Багаж</span>
					<div  class="col-xs-4">
                                            <span  class="cls-cargo-cost-info"> </span>
                                            <input class="cls-cargo-cost-info" value="" name="cargo-cost-info" type=hidden>
					</div>
				</div>
				<div class="row" style="$dealers_style">
                                        <span class="col-xs-8 control-label">В т.ч. комиссия агента</span>
					<div  class="col-xs-4 small">
                                            <span class="cls-comission-in-cost-info"> </span>
					</div>
				</div>
				<div class="row" style="$dealers_style">
                                        <span class="col-xs-8 control-label">Наценка агента</span>
					<div  class="col-xs-4">
                                            <span  class="cls-comission-cost-info"> </span>
                                            <input class="cls-comission-cost-info" value="" name="comission-cost-info" type=hidden>
					</div>
				</div>
				<div class="row" style="margin-top:20px;">
                                        <span class="col-xs-8 control-label">Итого</span>
					<div  class="col-xs-4">
                                            <span  class="cls-total-cost-info"> </span>
                                            <input class="cls-total-cost-info" value="" name="total-cost-info" type=hidden>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
S;
