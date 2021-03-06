<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>


<form class="form-horizontal">
    <fieldset>
		<div class="form-group">
            <label class="col-lg-4 control-label">Mode : </label>
            <div class="col-lg-4">
				<select class="configKey form-control" id="select_mode" data-l1key="externalDeamon">
                    <option value="0">Local</option>
                    <option value="2">Modem sur un Jeedom Esclave</option>
					<option value="1">Jeedom Esclave (Envoyer les données sur le jeedom Master)</option>
                </select>
            </div>
        </div>
		<div id="div_local" class="form-group">
            <label class="col-lg-4 control-label">Port Série (Local) :</label>
            <div class="col-lg-4">
                <select id="select_port" class="configKey form-control" data-l1key="port">
                    <option value="">Aucun</option>
                    <?php
                    foreach (jeedom::getUsbMapping() as $name => $value) {
                        echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
                    }
					echo '<option value="serie">Modem Série</option>';
                    ?>
                </select>
				
				<input id="port_serie" class="configKey form-control" data-l1key="modem_serie_addr" style="margin-top:5px;display:none" placeholder="Renseigner le port série (ex : /dev/ttyS0)"/>
				<script>
				$( "#select_port" ).change(function() {
					$( "#select_port option:selected" ).each(function() {
						if($( this ).val() == "serie"){
						 $("#port_serie").show();
						}
						else{
							$("#port_serie").hide();
							}
						});
					
				});
				$( "#select_mode" ).change(function() {
					$( "#select_mode option:selected" ).each(function() {
						if($( this ).val() == "0" || $( this ).val() == "1"){
						 $("#div_local").show();
						}
						else{
							$("#div_local").hide();
							}
						});
				});
			</script>
            </div>
        </div>
    </fieldset>
</form>