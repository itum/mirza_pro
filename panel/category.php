<?php
session_start();
ini_set('error_log', 'error_log');
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';

// Check session first before any database queries
if( !isset($_SESSION["user"]) ){
    header('Location: login.php');
    exit;
}

try {
    $query = $pdo->prepare("SELECT * FROM admin WHERE username=:username");
    $query->bindParam("username", $_SESSION["user"], PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    
    if(!$result){
        header('Location: login.php');
        exit;
    }
    
    $query = $pdo->prepare("SELECT * FROM category ORDER BY id DESC");
    $query->execute();
    $listcategory = $query->fetchAll();
} catch (PDOException $e) {
    error_log("Database error in category.php (initial queries): " . $e->getMessage());
    die("خطا در اتصال به پایگاه داده");
}

// Handle add category
$categoryName = $_POST['category_name'] ?? null;
if(!empty($categoryName)){
    try {
        $categoryName = trim($categoryName);
        if(strlen($categoryName) > 500){
            $_SESSION['error_message'] = "نام دسته نباید بیشتر از 500 کاراکتر باشد";
            header("Location: category.php");
            exit;
        }
        
        // Check if category already exists
        $existingCategory = select("category", "*", "remark", $categoryName, "count");
        if($existingCategory != 0){
            $_SESSION['error_message'] = "دسته با این نام از قبل وجود دارد";
            header("Location: category.php");
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO category (remark) VALUES (:remark)");
        $stmt->bindValue(':remark', $categoryName, PDO::PARAM_STR);
        $stmt->execute();
        $_SESSION['success_message'] = "دسته با موفقیت اضافه شد";
        header("Location: category.php");
        exit;
    } catch (PDOException $e) {
        error_log("Database error in category.php (add category): " . $e->getMessage());
        $_SESSION['error_message'] = "خطا در افزودن دسته: " . $e->getMessage();
        header("Location: category.php");
        exit;
    } catch (Exception $e) {
        error_log("General error in category.php (add category): " . $e->getMessage());
        $_SESSION['error_message'] = "خطا در افزودن دسته";
        header("Location: category.php");
        exit;
    }
}

// Handle edit category
if(isset($_GET['editid']) && $_GET['editid'] !== ''){
    $editId = intval($_GET['editid']);
    $editCategory = select("category", "*", "id", $editId, "select");
    if(!$editCategory){
        $_SESSION['error_message'] = "دسته پیدا نشد";
        header("Location: category.php");
        exit;
    }
}

if(isset($_POST['edit_category_name']) && isset($_POST['edit_category_id'])){
    try {
        $editId = intval($_POST['edit_category_id']);
        $editCategoryName = trim($_POST['edit_category_name']);
        
        if(strlen($editCategoryName) > 500){
            $_SESSION['error_message'] = "نام دسته نباید بیشتر از 500 کاراکتر باشد";
            header("Location: category.php?editid=" . $editId);
            exit;
        }
        
        // Check if another category with same name exists
        $existingCategory = select("category", "*", "remark", $editCategoryName, "select");
        if($existingCategory && $existingCategory['id'] != $editId){
            $_SESSION['error_message'] = "دسته دیگری با این نام وجود دارد";
            header("Location: category.php?editid=" . $editId);
            exit;
        }
        
        update("category", "remark", $editCategoryName, "id", $editId);
        $_SESSION['success_message'] = "دسته با موفقیت ویرایش شد";
        header("Location: category.php");
        exit;
    } catch (Exception $e) {
        error_log("Error in category.php (edit category): " . $e->getMessage());
        $_SESSION['error_message'] = "خطا در ویرایش دسته";
        header("Location: category.php");
        exit;
    }
}

// Handle delete category
if(isset($_GET['removeid']) && $_GET['removeid'] !== ''){
    try {
        $removeId = intval($_GET['removeid']);
        
        // Check if category is used in products
        $productsUsingCategory = select("product", "*", "category", select("category", "remark", "id", $removeId, "select")['remark'] ?? '', "count");
        if($productsUsingCategory > 0){
            $_SESSION['error_message'] = "این دسته در محصولات استفاده شده و نمی‌توان آن را حذف کرد";
            header("Location: category.php");
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM category WHERE id = :id");
        $stmt->bindParam(':id', $removeId, PDO::PARAM_INT);
        $stmt->execute();
        $_SESSION['success_message'] = "دسته با موفقیت حذف شد";
        header("Location: category.php");
        exit;
    } catch (PDOException $e) {
        error_log("Database error in category.php (remove category): " . $e->getMessage());
        $_SESSION['error_message'] = "خطا در حذف دسته";
        header("Location: category.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Mosaddek">
    <meta name="keyword" content="FlatLab, Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
    <link rel="shortcut icon" href="img/favicon.html">

    <title>پنل مدیریت ربات میرزا</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/jquery-easy-pie-chart/jquery.easy-pie-chart.css" rel="stylesheet" type="text/css" media="screen"/>
    <link rel="stylesheet" href="css/owl.carousel.css" type="text/css">
    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet" />

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
  </head>

<body>

    <section id="container" class="">
<?php include("header.php");
?>
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <!-- page start-->
                <div class="row">
                    <div class="col-lg-12">
                        <section class="panel">
                            <header class="panel-heading">لیست دسته‌ها</header>
                                <section class="panel">
                                <a href="#addcategory" data-toggle="modal"  class="btn btn-info  btn-sm">اضافه کردن دسته</a>
                        </section>
                        <?php
                        if(isset($_SESSION['error_message'])){
                            echo '<div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    ' . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') . '
                                  </div>';
                            unset($_SESSION['error_message']);
                        }
                        if(isset($_SESSION['success_message'])){
                            echo '<div class="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    ' . htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') . '
                                  </div>';
                            unset($_SESSION['success_message']);
                        }
                        ?>
                            <table class="table table-striped border-top" id="sample_1">
                                <thead>
                                    <tr>
                                        <th class="hidden-phone">شناسه</th>
                                        <th class="hidden-phone">نام دسته</th>
                                        <th class="hidden-phone">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody> <?php
                                foreach($listcategory as $category){
                                   echo "<tr class=\"odd gradeX\">
                                        <td>{$category['id']}</td>
                                        <td class=\"hidden-phone\">{$category['remark']}</td>
                                        <td  class=\"hidden-phone\"><a class = \"btn btn-danger\" href= \"category.php?removeid={$category['id']}\" onclick=\"return confirm('آیا از حذف این دسته اطمینان دارید؟')\">حذف دسته</a><a class = \"btn btn-info\" href= \"category.php?editid={$category['id']}\">ویرایش دسته</a></td>
                                    </tr>";
                                }
                                    ?>
                                </tbody>
                            </table>
                        </section>
                    </div>
                </div>
                <!-- page end-->
            </section>
        </section>
        
        <!-- Add Category Modal -->
        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="addcategory" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                        <h4 class="modal-title">اضافه کردن دسته</h4>
                    </div>
                    <div class="modal-body">
                        <form action = "category.php" method = "POST" class="form-horizontal" role="form">
                            <div class="form-group">
                                <label for="category_name" class="col-lg-2 control-label">نام دسته</label>
                                <div class="col-lg-10"><input required type="text" name = "category_name" class="form-control" id="category_name" maxlength="500"></div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button type="submit" class="btn btn-danger">اضافه کردن دسته</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <?php if(isset($editCategory)): ?>
        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="editcategory" class="modal fade in" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button" onclick="window.location.href='category.php'">×</button>
                        <h4 class="modal-title">ویرایش دسته</h4>
                    </div>
                    <div class="modal-body">
                        <form action = "category.php" method = "POST" class="form-horizontal" role="form">
                            <input type="hidden" name="edit_category_id" value="<?php echo $editCategory['id']; ?>">
                            <div class="form-group">
                                <label for="edit_category_name" class="col-lg-2 control-label">نام دسته</label>
                                <div class="col-lg-10"><input required type="text" name = "edit_category_name" class="form-control" id="edit_category_name" value="<?php echo htmlspecialchars($editCategory['remark'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="500"></div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button type="submit" class="btn btn-success">ذخیره تغییرات</button>
                                    <a href="category.php" class="btn btn-default">انصراف</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $('#editcategory').modal('show');
        </script>
        <?php endif; ?>

        <!--main content end-->
    </section>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.scrollTo.min.js"></script>
    <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
    <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>

    <!--common script for all pages-->
    <script src="js/common-scripts.js"></script>

    <!--script for this page only-->
    <script src="js/dynamic-table.js"></script>

</body>
</html>
