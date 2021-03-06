<?php

$query_array=array("headline"=>__("List Systems for Monitormodell/Manufacturer"),
                   "sql"=>"SELECT monitor_id, monitor_model, monitor_manufacturer, monitor_serial, system_name, system_uuid FROM monitor, system WHERE monitor_model = '".$_GET["modell"]."' AND monitor_manufacturer = '".$_GET["manufacturer"]."' AND monitor_uuid = system_uuid AND monitor_timestamp = system_timestamp ",
                   "sort"=>"system_name",
                   "dir"=>"ASC",
                   "get"=>array("file"=>"system.php",
                                "title"=>__("Go to System"),
                                "var"=>array("monitor"=>"%monitor_id",
                                             "view"=>"monitor",
                                            ),
                               ),
                   "fields"=>array("10"=>array("name"=>"other_linked_pc",
                                               "head"=>__("UUID"),
                                               "show"=>"n",
                                              ),
                                   "20"=>array("name"=>"system_name",
                                               "head"=>__("Attached Device"),
                                               "show"=>"y",
                                               "link"=>"y",
                                               "get"=>array("file"=>"system.php",
                                                            "var"=>array("pc"=>"%system_uuid",
                                                                         "view"=>"summary",
                                                                        ),
                                                           ),
                                              ),
                                   "30"=>array("name"=>"monitor_manufacturer",
                                               "head"=>__("Manufacturer"),
                                               "show"=>"y",
                                               "link"=>"n",
                                              ),
                                   "40"=>array("name"=>"monitor_model",
                                               "head"=>__("Model"),
                                               "show"=>"y",
                                               "link"=>"y",
                                              ),
                                   "50"=>array("name"=>"monitor_serial",
                                               "head"=>__("Serial"),
                                               "show"=>"y",
                                               "link"=>"y",
                                              ),
                                  ),
                  );
?>
