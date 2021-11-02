<?php
return <<<S
        <div class="panel">
            <div class="panel-body">
                <div>
                    <b>Скопируйте эту ссылку и отправьте своим пассажирам.</b>
                    <br>
                    <input class="form-control" name=link value="{$link}" readonly>
                    <br>
                    Ссылка открывает приватную страницу, на которой Ваш пассажир сможет выбрать места
                    и заполнить данные о себе (для посадочной ведомости).
                    <br><br>
                    <b>ВНИМАНИЕ!!!</b><br>
                    <b>1.</b> Во избежание недоразумений с заполнением автобуса отправляйте эту ссылку только по закрытым каналам!
                    <br>
                    <b>2.</b> Вы можете ограничить срок действия ссылки, изменив его. Это можно делать несколько раз до момента отправления автобуса.
                    <br>
                    <br>
                </div>

                <div class="input-group">
                    <span class="input-group-addon"><b>Ссылка действительна до:</b></span>
                    <input class="form-control center" name="actual_to_date" value="{$date}">
                    <input class="form-control center" name="actual_to_time" value="{$time}">
                </div>
            </div>
        </div>
S;
