<?php $pageTitle = " transaction_type - Create & Update all transaction_types "; ?>
<?php include_once("transaction_type.inc"); ?>
<?php
$page = !(empty($_GET['page'])) ? (int) $_GET['page'] : 1;
if (!(empty($_GET['per_page']))) {
  if ($_GET['per_page'] == "all") {
    $per_page = "";
  } else {
    $per_page = (int) $_GET['per_page'];
  }
} else {
  $per_page = 10;
}
?>
<?php
$transaction_type = new transaction_type;
//$field array represents all the fields in the class
$field_array = transaction_type::$field_array;
foreach ($field_array as $key => $value) {
  $transaction_type->$value = "";
}
$msg = "";

$result = array();
//search array is used for srach fields & while condition in SQL query
$search_array = transaction_type::$field_array;
foreach ($search_array as $key => $value) {
  if (empty($_GET[$value])) {
    $_GET[$value] = "";
  }
}

if (!empty($_SERVER['QUERY_STRING'])) {
  $query_string = $_SERVER['QUERY_STRING'];
//  $query_string = remove_querystring_var($query_string, 'page');
  if (!empty($_GET['page'])) {
    $query_string = substr($query_string, 7);
  }
} else {
  $query_string = "";
}

//Column array represents all the fixed coulmns in result table
if (empty($_GET['column_array'])) {
  $column_array = ["transaction_type_id",
      "class",
      "transaction_type",
      "description",
      "primary",
      "created_by",
      "creation_date"
  ];
}
?>



<?php
if (!empty($_GET["transaction_type_id"])) {
  $transaction_type_id = $_GET["transaction_type_id"];
  $transaction_type = transaction_type::find_by_id($transaction_type_id);
}
?>

<?php
$whereFields = array();

if ((!empty($_GET['submit_search'])) && empty($_GET["transaction_type_id"])) {
  if (!empty($_GET['new_column'])) {
    $new_column = $_GET['new_column'];
    array_push($column_array, $new_column);
  }

  foreach ($search_array as $key => $value) {
    if (!empty($_GET[$value])) {
      $whereFields[] = sprintf("`%s` LIKE '%%%s%%'", $value, trim(mysql_prep($_GET[$value])));
    } else {
      $msg = "No criteria entered";
    }
  }

  if (count($whereFields) > 0) {

    // Construct the WHERE clause by gluing the fields
    // together with a " AND " separator.
    $whereClause = "WHERE " . implode(" AND ", $whereFields);

    // And then create the SQL query itself.
    $sql = "SELECT * FROM transaction_type " . $whereClause;
    $count_sql = "SELECT COUNT(*) FROM transaction_type " . $whereClause;
  } else {
    $sql = "SELECT * FROM transaction_type ";
    $count_sql = "SELECT COUNT(*) FROM transaction_type ";
  }

  $total_count = transaction_type::count_all_by_sql($count_sql);

  if (!empty($per_page)) {
    $pagination = new pagination($page, $per_page, $total_count);
    $sql .=" LIMIT {$per_page} ";
    $sql .=" OFFSET {$pagination->offset()}";
  }

  $result = transaction_type::find_by_sql($sql);
}
?>

<?php
$msg = array();
if (!empty($_POST['submit_transaction_type']) && empty($_POST['download'])) {
  for ($i = 0; $i < count($_POST['transaction_type_id']); $i++) {
    $transaction_type = new transaction_type;

    foreach ($field_array as $key => $value) {
      if (!empty($_POST[$value])) {
        $transaction_type->$value = trim(mysql_prep($_POST[$value][$i]));
      } else {
        $transaction_type->$value = "";
      }
    }
//  $transaction_type->class = trim(mysql_prep($_POST['transaction_type_class']));
//  echo '<br/> transaction_type_class is : '. trim(mysql_prep($_POST['transaction_type_class']));
    if (isset($_POST['primary'][$i])) {
      $transaction_type->$value = 1;
    } else {
      $transaction_type->$value = 0;
    }

    $time = time();
    $transaction_type->creation_date = strftime("%d-%m-%Y %H:%M:%S", $time);
    $transaction_type->created_by = $_SESSION['user_name'];
    $transaction_type->last_update_date = $transaction_type->creation_date;
    $transaction_type->last_update_by = $transaction_type->created_by;

//for new transaction_type creation the transaction_type id should be null 

    if (empty($transaction_type->transaction_type) || empty($transaction_type->class) || empty($transaction_type->description) || empty($transaction_type->primary_transaction_type)) {
      $newMsg = "transaction_type, Class or Description is Blank";
      array_push($msg, $newMsg);
    } else {
      $new_transaction_type_entry = $transaction_type->save();
      if ($new_transaction_type_entry == 1) {
        $newMsg = 'transaction_type is sucessfully saved';
        array_push($msg, $newMsg);
      }//end of transaction_type entry & msg
      else {
        $newMsg = "Record coundt be saved!!" . mysql_error() .
                ' Returned Value is : ' . $new_transaction_type_entry;
        array_push($msg, $newMsg);
      }//end of transaction_type insertion else
    }//end of transaction_type check & new transaction_type creation
    //reset all accounts to accounts from id
  }
//  complete of for loop
}//end of post submit header
?>

<div id="structure">
  <div id="transaction_type">
    <div id="form_top">
      <ul class="form_top">
        <li><input type="button" class="button refresh" value="Refresh" name="refresh"/> </li>
        <li> <a class="button" href="transaction_type.php">New Object</a> </li>
        <li><input type="button" id="add_object" class="button" value="New Line" name="add_object"/> </li>
        <li><input type="submit" form="transaction_type_header" name="submit_transaction_type" id="submit_transaction_type" class="button" Value="Save"></li>
        <li> <input type="button" class="button remove_row" id="remove_row" Value="Remove"></li>
        <li> <input type="submit" class="button delete" form="coa_combination_form" name="delete_row" id="delete_row" value="Delete"></li>
        <li><input type="reset" class="button" form="transaction_type_header" name="reset" Value="Reset All"></li>
        <li><script>document.write('<a class="button" href="' + document.referrer + '">Go Back</a>');</script></li>
      </ul>
    </div>


    <!--    START OF FORM HEADER-->
    <div id ="form_header">


      <ul id="form_box"> 
        <li>   <!--    Place for showing error messages-->
          <?php
          if (!empty($msg)) {
            echo '<span class="error">';
            if (is_array($msg)) {
              foreach ($msg as $key => $value) {
                $x = $key + 1;
                echo 'Message ' . $x . ' : ' . $value . '<br />';
              }
            } else {
              echo $msg;
            }

            echo '</span>';
          }
          ?> 
          <!--    End of place for showing error messages-->
        </li>
        <!--Search form creation    -->
        <li>
          <div id="transaction_type_search">
            <br>
            <form action="transaction_type.php" name="search_transaction_type" method="GET" class="search_box transaction_type_form" id="search_transaction_type">
              <ul class="search_form">                   
                <li><label>transaction_type Id : </label>
                  <input type="text" id="transaction_type_id" name="transaction_type_id" value="<?php
          echo!(is_array($_GET['transaction_type_id'])) ? htmlentities($_GET['transaction_type_id']) : "";
          ?>" 
                         maxlength="50" >
                </li>
                <li><label>transaction_type Class : </label>
                  <input type="search" name="class" id="class" value="<?php
          echo!(is_array($_GET['class'])) ? htmlentities($_GET['class']) : "";
          ?>" 
                         maxlength="50" >
                </li>
                <li><label>Unit of measure : </label>
                  <input type="search" name="transaction_type" id="transaction_type" value="<?php
          echo!(is_array($_GET['transaction_type'])) ? htmlentities($_GET['transaction_type']) : "";
          ?>" 
                         maxlength="50" >
                </li>
                <li>
                  <label>Description : </label>
                  <input id="description" type="search" maxlength="50" value="<?php
          echo!(is_array($_GET['description'])) ? htmlentities($_GET['description']) : "";
          ?>" name="description">
                </li>
                <li>
                <lable>Add dynamic search criteria </lable>
                <select name="new_search_criteria" id="new_search_criteria" class="new_search_criteria"> 
                  <option value=""> </option>
<?php
foreach ($search_array as $key => $value) {
  echo '<option value="' . htmlentities($value) . '"';
  echo '>' . $value . '</option>';
}
?> 
                </select>
                </li>
                <li>
                  <input type="button" class="add button" id="new_search_criteria_add" value="Add">
                </li>
                <!--          send the existing column array to POST-->
                <li><input type="hidden" name="column_array" id="column_array" value="<?php print base64_encode(serialize($column_array)) ?>" >
                </li>
              </ul>
              <ul class="add_new_search">
                <li>
                <lable>Add a new column</lable>
                <select name="new_column" id="new_column" > 
                  <option value=""> </option>
                  <?php
                  foreach ($search_array as $key => $value) {
                    echo '<option value="' . htmlentities($value) . '"';
                    echo '>' . $value . '</option>';
                  }
                  ?> 
                 </select>
                </li>
                <li>
                <lable>Records per page</lable>
                <select name="per_page" id="per_page" > 
                  <option value="10">10</option>
                  <option value="20" <?php echo $per_page == 20 ? "selected" : "" ?>>20</option>
                  <option value="50" <?php echo $per_page == 50 ? "selected" : "" ?>>50</option>
                  <option value="100" <?php echo $per_page == 100 ? "selected" : "" ?>>100</option>
                  <option value="1000" <?php echo $per_page == 1000 ? "selected" : "" ?>>1000</option>
                  <option value="all" <?php echo $per_page == "all" ? "selected" : "" ?>>All</option>
                  <option value="1" <?php echo $per_page == "1" ? "selected" : "" ?>>1</option>
                </select>

                </li>
              </ul>
              <ul class="form_buttons">
                <li><a href="transaction_type.php" class="reset button"> Reset All</a></li>
                <li><input type="submit" form="search_transaction_type" name="submit_search" class="search button" value="Search"></li>

              </ul>
            </form> 

          </div>
        </li>
        <li>
          <div id="scrollElement">
            <form action="transaction_type.php"  method="post" id="transaction_type_header"  name="transaction_type_header">
<?php if (!empty($result)) {
  ?>
                <?php
                if (!empty($total_count)) {
                  echo '<h3>Total records : ' . $total_count . '</h3>';
                }
                ?>
                <table class="form_table">
                  <thead> 
                    <tr>
                      <th>Action</th>
                      <th>transaction_type Id</th>
                      <th>transaction_type</th>
                      <th>Class</th>
                      <th>Description</th>
                      <th>Primary</th>
                      <th>Primary transaction_type</th>
                      <th>Operator</th>
                      <th>Primary Relation</th>
                      <th>EF Id</th>
                      <th>Status</th>
                      <th>Rev Enabled</th>
                      <th>Rev#</th>
                    </tr>
                  </thead>
                  <tbody id="transaction_type_values">
  <?php
  $count = 0;
  foreach ($result as $transaction_type) {
    ?>         
                      <tr id="transaction_type_values<?php echo $count ?>">
                        <td>    
                          <ul class="inline_action">
                            <li class="add_row_img"><img  src="<?php echo HOME_URL; ?>themes/images/add.png"  alt="add new line" /></li>
                            <li class="remove_row_img"><img src="<?php echo HOME_URL; ?>themes/images/remove.png" alt="remove this line" /> </li>
                            <li><input type="checkbox" name="transaction_type_id_cb" value="<?php echo htmlentities($transaction_type->transaction_type_id); ?>"></li>           
                          </ul>
                        </td>
                        <td>
                          <input type="text" readonly name="transaction_type_id[]" 
                                 value="<?php echo htmlentities($transaction_type->transaction_type_id); ?>" size="10"                           
                                 maxlength="50" class="transaction_type_id" placeholder="Sys generated number"> 
                        </td>
                        <td>    
                          <input type="text" name="transaction_type[]" value="<?php echo htmlentities($transaction_type->transaction_type); ?>" 
                                 size="10" maxlength="10"  id="transaction_type"> 
                        </td>
                        <td>    
                          <select name="class[]" id="class" class="class"> 
                            <option value="" ></option> 
    <?php
    $class = transaction_type::transaction_type_class();
    foreach ($class as $records) {
      echo '<option value="' . $records->option_line_id . '"';
      echo $records->option_line_id == $transaction_type->class ? ' selected' : ' ';
      echo '>' . $records->option_line_code . '</option>';
    }
    ?> 
                          </select> 
                        </td>
                        <td>
                          <input type="text" name="description[]" value="<?php echo htmlentities($transaction_type->description); ?>" 
                                 size="15" maxlength="150"  id="description"> 
                        </td>
                        <td>
                          <input type="checkbox" name="primary[]" 
                                 value="<?php echo (empty($transaction_type->primary)) ? "1" : ""; ?>"  id="primary"
    <?php
    if ($transaction_type->rev_enabled == 1) {
      echo " checked";
    } else {
      echo "";
    }
    ?> > 
                        </td>
                        <td>
                          <input type="text" name="primary_transaction_type[]" value="<?php echo htmlentities($transaction_type->primary_transaction_type); ?>" 
                                 size="10" maxlength="150"  id="primary_transaction_type"> 
                        </td>
                        <td>  
                          <input type="image"  src="<?php echo HOME_URL; ?>themes/images/multiply.png" alt="multiply"/> 
                        </td>
                        <td>
                          <input type="text" name="primary_relation[]" value="<?php echo htmlentities($transaction_type->primary_relation); ?>" 
                                 size="10" maxlength="150"  id="primary_relation"> 
                        </td>
                        <td>
                          <input type="text" name="ef_id[]" value="<?php echo htmlentities($transaction_type->ef_id); ?>" 
                                 size="5" maxlength="10"  id="ef_id"> 
                        </td>
                        <td>                      
                          <Select name="status[]" id="status" >
                            <option value="" ></option>
                            <option value="enabled" 
    <?php echo $transaction_type->status == 'enabled' ? 'selected' : ''; ?> >Enabled</option>
                            <option value="disabled" 
                                    <?php echo $transaction_type->status == 'disabled' ? 'selected' : ''; ?> >Disabled</option>                                   
                          </select>

                        </td>
                        <td>
                          <input type="checkbox" name="rev_enabled[]" 
                                 value="<?php echo (empty($transaction_type->rev_enabled)) ? "1" : ""; ?>"  id="rev_enabled"
    <?php
    if ($transaction_type->rev_enabled == 1) {
      echo " checked";
    } else {
      echo "";
    }
    ?> >  
                        </td> 
                        <td>
                          <input type="text" name="rev_number[]" value="<?php echo htmlentities($transaction_type->rev_number); ?>" 
                                 maxlength="50" size="5"  id="rev_number" class="rev_number"> 
                        </td> 
    <?php
  }
  $count = $count + 1;
  ?>
                    </tr>
                  </tbody>
                  <!--                  Showing a blank form for new entry-->

                </table>

<?php } else { ?>

                <li>
                  <ul>
                    <li class="ncontrol"><span class="heading">Unit of measures </span> 
                      <div>
                        <table class="form_table">
                          <thead> 
                            <tr>
                              <th>Action</th>
                              <th>transaction_type Id</th>
                              <th>transaction_type</th>
                              <th>Class</th>
                              <th>Description</th>
                              <th>Primary</th>
                              <th>Primary transaction_type</th>
                              <th>Operator</th>
                              <th>Primary Relation</th>
                              <th>EF Id</th>
                              <th>Status</th>
                              <th>Rev Enabled</th>
                              <th>Rev#</th>
                            </tr>
                          </thead>
                          <tbody id="transaction_type_values">
                            <tr id="transaction_type_values0">
                              <td>    
                                <ul class="inline_action">
                                  <li class="add_row_img"><img  src="<?php echo HOME_URL; ?>themes/images/add.png"  alt="add new line" /></li>
                                  <li class="remove_row_img"><img src="<?php echo HOME_URL; ?>themes/images/remove.png" alt="remove this line" /> </li>
                                  <li><input type="checkbox" name="transaction_type_id_cb" value="<?php echo htmlentities($transaction_type->transaction_type_id); ?>"></li>           
                                </ul>
                              </td>
                              <td>
                                <input type="text" readonly name="transaction_type_id[]" 
                                       value="<?php echo htmlentities($transaction_type->transaction_type_id); ?>" size="10"                           
                                       maxlength="50" class="transaction_type_id" placeholder="Sys generated number"> 
                              </td>
                              <td>    
                                <input type="text" name="transaction_type[]" value="<?php echo htmlentities($transaction_type->transaction_type); ?>" 
                                       size="10" maxlength="10"  id="transaction_type"> 
                              </td>
                              <td>    
                                <select name="class[]" id="class" class="class"> 
                                  <option value="" ></option> 
  <?php
  $class = transaction_type::transaction_type_class();
  foreach ($class as $records) {
    echo '<option value="' . $records->option_line_id . '"';
    echo $records->option_line_id == $transaction_type->class ? ' selected' : ' ';
    echo '>' . $records->option_line_code . '</option>';
  }
  ?> 
                                </select> 
                              </td>
                              <td>
                                <input type="text" name="description[]" value="<?php echo htmlentities($transaction_type->description); ?>" 
                                       size="15" maxlength="150"  id="description"> 
                              </td>
                              <td>
                                <input type="checkbox" name="primary[]" 
                                       value="<?php echo (empty($transaction_type->primary)) ? "1" : ""; ?>"  id="primary"
  <?php
  if ($transaction_type->rev_enabled == 1) {
    echo " checked";
  } else {
    echo "";
  }
  ?> > 
                              </td>
                              <td>
                                <input type="text" name="primary_transaction_type[]" value="<?php echo htmlentities($transaction_type->primary_transaction_type); ?>" 
                                       size="10" maxlength="150"  id="primary_transaction_type"> 
                              </td>
                              <td>  
                                <input type="image"  src="<?php echo HOME_URL; ?>themes/images/multiply.png" alt="multiply"/> 
                              </td>
                              <td>
                                <input type="text" name="primary_relation[]" value="<?php echo htmlentities($transaction_type->primary_relation); ?>" 
                                       size="10" maxlength="150"  id="primary_relation"> 
                              </td>
                              <td>
                                <input type="text" name="ef_id[]" value="<?php echo htmlentities($transaction_type->ef_id); ?>" 
                                       size="5" maxlength="10"  id="ef_id"> 
                              </td>
                              <td>                      
                                <Select name="status[]" id="status" >
                                  <option value="" ></option>
                                  <option value="enabled" 
  <?php echo $transaction_type->status == 'enabled' ? 'selected' : ''; ?> >Enabled</option>
                                  <option value="disabled" 
                                          <?php echo $transaction_type->status == 'disabled' ? 'selected' : ''; ?> >Disabled</option>                                   
                                </select>

                              </td>
                              <td>
                                <input type="checkbox" name="rev_enabled[]" 
                                       value="<?php echo (empty($transaction_type->rev_enabled)) ? "1" : ""; ?>"  id="rev_enabled"
  <?php
  if ($transaction_type->rev_enabled == 1) {
    echo " checked";
  } else {
    echo "";
  }
  ?> >  
                              </td> 
                              <td>
                                <input type="text" name="rev_number[]" value="<?php echo htmlentities($transaction_type->rev_number); ?>" 
                                       maxlength="50" size="5"  id="rev_number" class="rev_number"> 
                              </td> 
                            </tr>
                          </tbody>
                          <!--                  Showing a blank form for new entry-->

                        </table>
                      </div>

                    </li>
                  </ul>
                </li>
              </form>
            </div>  

          </li>


  <?php }
?>

      </ul>
      <div id="pagination" style="clear: both;">
<?php
if (isset($pagination)) {
  if ($pagination->has_previous_page()) {
    echo "<a href=\"transaction_type.php?page=";
    echo $pagination->previous_page() . '&' . $query_string;
    echo "\"> &laquo; Previous </a> ";
  }

  for ($i = 1; $i <= $pagination->total_pages(); $i++) {
    if ($i == $page) {
      echo " <span class=\"selected\">{$i}</span> ";
    } else {
      echo " <a href=\"transaction_type.php?page={$i}&" . remove_querystring_var($query_string, 'page');
      echo '&submit_search=Search';
      echo '\">' . $i . '</a>';
    }
  }

  if ($pagination->has_next_page()) {
    echo " <a href=\"transaction_type.php?page=";
    echo $pagination->next_page() . '&' . remove_querystring_var($query_string, 'page');
    echo '&submit_search=Search';
    echo "\">Next &raquo;</a> ";
  }
}
?>
      </div>


      <!--download page creation-->
      <ul class="data_export">
        <li> <input type="submit" class="download button excel" value="<?php echo $per_page ?> Records" form="download"></li>
        <li> <input type="submit" class="download button excel" value="All Records" form="download_all"></li>
        <li> <input type="button" class="download button print" value="Print"></li>
      </ul>

<?php
if (!empty($sql)) {
  $transaction_type_obj = transaction_type::find_by_sql($sql);
  $transaction_type_array = json_decode(json_encode($transaction_type_obj), true);
}
?>
      <!--download page form-->
      <form action="<?php echo HOME_URL; ?>download.php" method="POST" name="download" id="download">
        <input type="hidden"  name="data" value="<?php print base64_encode(serialize($transaction_type_array)) ?>" >

      </form>

      <!--download page creation for all records-->
<?php
if (!empty($all_download_sql)) {
  $transaction_type_obj_all = transaction_type::find_by_sql($all_download_sql);
  $transaction_type_array_all = json_decode(json_encode($transaction_type_obj_all), true);
}
?>
      <!--download page form-->
      <form action="<?php echo HOME_URL; ?>download.php" method="POST" name="download_all" id="download_all">
        <input type="hidden"  name="data" value="<?php print base64_encode(serialize($transaction_type_array_all)) ?>" >
      </form>
      <!--download page completion-->


    </div>
    <!--END OF FORM HEADER-->  
  </div>
</div>
<!--   end of structure-->

<?php include_template('footer.inc') ?>