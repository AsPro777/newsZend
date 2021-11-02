<?php
return <<<S
        <div class="panel">
            <div class="panel-body">

                <div class="row" style="margin-bottom:12px">
                    <div class="col-md-5">
                        <label for="city" style="font-weight:bolder; font-size: 14px; margin-top: 7px;">Выберите город:</label>
                    </div>
                    <div class="col-md-7">
                        <input name="city" class="form-control" type="text">
                        <input name="id_city" value="0" type="hidden">
                        <input name="id_country" value="0" type="hidden">
                        <input name="x" value="0" type="hidden">
                        <input name="y" value="0" type="hidden">
                        <input name="address" value="0" type="hidden">
                    </div>

                </div>

                <div class="row" style="text-align:center">
                    <div id="ymaps" style="width: 100%; height: 400px; padding: 0; border:1px lightgray solid"></div>
                </div>

                <div class="row" id="point-name" style="margin-top:10px">
                    <div class="col-md-5">
                      <label for="name" style="font-weight:bolder; font-size: 14px; margin-top: 7px;">Укажите наименование остановки:</label>
                    </div>
                    <div class="col-md-7">
                      <input name="name" class="form-control" value="" type="text">
                    </div>
                </div>

            </div>
        </div>


<style>
table.table-map-balloon
{
    font-size:10px;
    max-width: 270px;
}
table.table-map-balloon tbody tr th,
table.table-map-balloon tbody tr td,
table.table-map-balloon thead tr th
{
    padding:0 2px;
}
table.table-map-balloon tbody tr td button.btn {
    margin-bottom: 0;
    padding: 4px 6px;
    font-size: 10px;
    line-height: 1;
    border-radius: 3px;
}
table.table-map-balloon tbody tr th i[class^="icon-"],
table.table-map-balloon tbody tr th i[class*=" icon-"]
{
    font-size: 8px;
    top:-5px;
    cursor: pointer;
    color: lightgray;
}
table.table-map-balloon tbody tr th i[class^="icon-"]:hover,
table.table-map-balloon tbody tr th i[class*=" icon-"]:hover
{
    color: gray;
}

table.table-map-balloon tbody tr td button.btn[name="bpmtags"],
table.table-map-balloon tbody tr td button.btn[name="bpmsave"],
table.table-map-balloon tbody tr td button.btn[name="bpmdel"]
{
    width: 75px;
}

</style>

S;
