<?php
if (!isConnect('admin')) {
    throw new Exception('Error 401 Unauthorized');
}
sendVarToJS('eqType', 'jeeserial');
$controlerState = jeeserial::getJeeSerialInfo('');
if($controlerState === ''){
   echo '<div class="alert jqAlert alert-danger" style="margin : 0px 5px 15px 15px; padding : 7px 35px 7px 15px;">{{Impossible de contacter le serveur jeeSerial. Avez vous bien renseigné l\'IP ?}}</div>'; 
}
$deamonRunning = false;
$deamonRunning = jeeserial::deamonRunning();
?>

<div class="row row-overflow">
    <div class="col-lg-2">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <center>
					<?php
                    if ($deamonRunning) {
                        echo "<a class='btn btn-default btn-sm tooltips' id='bt_stopJeeSerialDaemon' title=\"Le démon est démarré. Forcer l'arrêt du démon jeeSerial\"><i class='fa fa-stop' style='color : red;'></i> <span class='expertModeHidden'>{{Arreter jeeSerial}}</span></a>";
                    }
                    ?>
                    <a class='btn btn-default btn-sm tooltips' id='bt_helpJeeSerial' title=\"Aide\"><i class='fa fa-question-circle' style='color : black;'></i> {{Aide jeeSerial}}</a>
                </center>
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="Rechercher" style="width: 100%"/></li>
                <?php
                foreach (eqLogic::byType('jeeserial') as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName() . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="col-lg-10 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <div class="row">
            <div class="col-lg-6">
                <form class="form-horizontal">
                    <fieldset>
                        <legend>Général</legend>
                        <div class="form-group">
                            <label class="col-lg-3 control-label">Nom de l'équipement</label>
                            <div class="col-lg-4">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="Nom de l'équipement"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label">ID</label>
                            <div class="col-lg-4">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="logicalId" placeholder="ID de l'équipement: emplacement mémoire"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"></label>
                            <div class="col-lg-1">
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/> Activer 
                                </label>
                            </div>
                            <label class="col-lg-1 control-label"></label>
                            <div class="col-lg-1">
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/> Visible 
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label" >Objet parent</label>
                            <div class="col-lg-4">
                                <select class="eqLogicAttr form-control" data-l1key="object_id">
                                    <option value="">Aucun</option>
                                    <?php
                                    foreach (object::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label">Catégorie</label>
                            <div class="col-lg-9">
                                <?php
                                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                    echo '<label class="checkbox-inline">';
                                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                    echo '</label>';
                                }
                                ?>

                            </div>
                        </div>
                    </fieldset> 
                </form>
            </div>
            <div class="col-lg-6">
                <form class="form-horizontal">
                    <fieldset>
                        <legend>Informations</legend>
                        <div class="form-group">
                            <label class="col-lg-4 control-label">Equipement pré-défini</label>
                            <div class="col-lg-8">
                                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="device">
                                    <option value="">Aucun</option>
                                    <?php
                                    foreach (jeeserial::devicesParameters() as $key => $info) {
                                        echo '<option value="' . $key . '">' . $info['name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group expertModeVisible">
                            <label class="col-lg-4 control-label">{{Délai maximum autorisé entre 2 messages (min)}}</label>
                            <div class="col-lg-4">
                                <input class="eqLogicAttr form-control" data-l1key="timeout" />
                            </div>
                        </div>
                    </fieldset> 
                </form>
            </div>
        </div>

        <legend>{{Commandes}}</legend>


        <a class="btn btn-success btn-sm cmdAction" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter une commande}}</a><br/><br/>
        <table id="table_cmd" class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th style="width: 300px;">{{Nom}}</th>
                    <th style="width: 130px;">{{Type}}</th>
                    <th class="expertModeVisible">{{Logical ID (info) ou Commande brute (action)}}</th>
                    <th >{{Paramètres}}</th>
                    <!--<th style="width: 200px;">{{Options}}</th>-->
                    <th></th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        <form class="form-horizontal">
            <fieldset>
                <div class="form-actions">
                    <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                    <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                </div>
            </fieldset>
        </form>

    </div>
</div>



<?php include_file('desktop', 'jeeserial', 'js', 'jeeserial'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>