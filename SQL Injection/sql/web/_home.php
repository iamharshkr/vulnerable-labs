<?php
include "config.php";
$error = '';

// Check user login or not
if (!isset($_SESSION['uname'])) {
    header('Location: index.php');
}

// logout
if (isset($_POST['but_logout'])) {
    session_destroy();
    header('Location: index.php');
}


if (isset($_POST['search-btn'])) {

    $uname = $_POST['search-txt'];
    if ($uname != "") {
        $uname = mysqli_real_escape_string($conn, $uname);

        $sql_query = "SELECT * FROM login WHERE username = '$uname'";
        $result = mysqli_query($conn, $sql_query) or die(mysqli_error($conn));
        if (mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_all($result);
        } else {
            $error = "No result found";
        }
    } else {
        $error =  "Username should not be empty";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="generator" content="Codeply">
    <title>CyberTronic: Bug Bounty</title>
    <base target="_self">

    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body,
        html {
            height: 100%;
        }

        /*
 * Off Canvas sidebar at medium breakpoint
 * --------------------------------------------------
 */
        @media screen and (max-width: 992px) {

            .row-offcanvas {
                position: relative;
                -webkit-transition: all 0.25s ease-out;
                -moz-transition: all 0.25s ease-out;
                transition: all 0.25s ease-out;
            }

            .row-offcanvas-left .sidebar-offcanvas {
                left: -33%;
            }

            .row-offcanvas-left.active {
                left: 33%;
                margin-left: -6px;
            }

            .sidebar-offcanvas {
                position: absolute;
                top: 0;
                width: 33%;
                height: 100%;
            }
        }

        /*
 * Off Canvas wider at sm breakpoint
 * --------------------------------------------------
 */
        @media screen and (max-width: 34em) {
            .row-offcanvas-left .sidebar-offcanvas {
                left: -45%;
            }

            .row-offcanvas-left.active {
                left: 45%;
                margin-left: -6px;
            }

            .sidebar-offcanvas {
                width: 45%;
            }
        }

        .card {
            overflow: hidden;
        }

        .card-body .rotate {
            z-index: 8;
            float: right;
            height: 100%;
        }

        .card-body .rotate i {
            color: rgba(20, 20, 20, 0.15);
            position: absolute;
            left: 0;
            left: auto;
            right: -10px;
            bottom: 0;
            display: block;
            -webkit-transform: rotate(-44deg);
            -moz-transform: rotate(-44deg);
            -o-transform: rotate(-44deg);
            -ms-transform: rotate(-44deg);
            transform: rotate(-44deg);
        }
    </style>
</head>

<body>
    <nav class="navbar fixed-top navbar-expand-md navbar-dark bg-primary mb-3">
        <div class="flex-row d-flex">
            <button type="button" class="navbar-toggler mr-2 " data-toggle="offcanvas" title="Toggle responsive left sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="home.php" title="Secret Dashboard By: CyberTronic">Secret Dashboard</a>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbar">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">Home</span></a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="" data-target="#myModal" data-toggle="modal">Welcome: <?php echo $_SESSION['name'] ? $_SESSION['name'] : '' ?></a>
                </li>
                <li class="nav-item">
                    <form method="post" class="nav-link" data-target="#myModal" data-toggle="modal">
                        <button name="but_logout" class="btn btn-danger" type="submit">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid" id="main">
        <div class="row row-offcanvas row-offcanvas-left">
            <div class="col-md-3 col-lg-2 sidebar-offcanvas bg-light pl-0" id="sidebar" role="navigation">
                <ul class="nav flex-column sticky-top pl-0 pt-5 mt-3">
                    <li class="nav-item"><a class="nav-link" href="#">Overview</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="#submenu1" data-toggle="collapse" data-target="#submenu1">Reports▾</a>
                        <ul class="list-unstyled flex-column pl-3 collapse" id="submenu1" aria-expanded="false">
                            <li class="nav-item"><a class="nav-link" href="">Report 1</a></li>
                            <li class="nav-item"><a class="nav-link" href="">Report 2</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#">Analytics</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Export</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Snippets</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Flexbox</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Layouts</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Templates</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Themes</a></li>
                    <li class="nav-item">
                        <form method="post" class="nav-link">
                            <button name="but_logout" class="btn btn-outline-primary" type="submit">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
            <!--/col-->

            <div class="col main pt-5 mt-3">
                <h1 class="display-4 d-none d-sm-block">
                    User Dashboard
                </h1>
                <strong>Holy <?php echo $_SESSION['name'] ? $_SESSION['name'] : '' ?>! </strong>Only hackers can capture flag.
                <div class="row mb-3">
                    <div class="col-xl-3 col-sm-6 py-2">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body bg-success">
                                <div class="rotate">
                                    <i class="fa fa-user fa-4x"></i>
                                </div>
                                <h6 class="text-uppercase">Users</h6>
                                <h1 class="display-4">134</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 py-2">
                        <div class="card text-white bg-danger h-100">
                            <div class="card-body bg-danger">
                                <div class="rotate">
                                    <i class="fa fa-list fa-4x"></i>
                                </div>
                                <h6 class="text-uppercase">Posts</h6>
                                <h1 class="display-4">87</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 py-2">
                        <div class="card text-white bg-info h-100">
                            <div class="card-body bg-info">
                                <div class="rotate">
                                    <i class="fa fa-twitter fa-4x"></i>
                                </div>
                                <h6 class="text-uppercase">Tweets</h6>
                                <h1 class="display-4">125</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 py-2">
                        <div class="card text-white bg-warning h-100">
                            <div class="card-body">
                                <div class="rotate">
                                    <i class="fa fa-share fa-4x"></i>
                                </div>
                                <h6 class="text-uppercase">Shares</h6>
                                <h1 class="display-4">36</h1>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/row-->
                <form method="post">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="input-group flex-nowrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="addon-wrapping">@</span>
                            </div>
                            <input type="text" class="form-control" name="search-txt" placeholder="Username" aria-label="Username" aria-describedby="addon-wrapping">
                            <button name="search-btn" class="btn btn-outline-success ml-2">Search</button>
                        </div>
                    </div>
                </form>
                <?php
                if (isset($error) && strlen($error) > 0) {
                    echo '<div class="my-2 alert alert-danger alert-dismissible fade show" role="alert">
                    ' . $error . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>';
                }
                ?>

                <hr>
                <div class="my-4 mx-auto">
                    <div class="col-lg-9 col-md-8">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="thead-inverse">
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Password</th>
                                        <th>Profile Link</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($row)) {
                                        $i = 1;
                                        foreach ($row as $value) {
                                            echo '<tr>
                                        <td>' . $i . '</td>
                                        <td>' . $value[1] . '</td>
                                        <td>' . $value[2] . '</td>
                                        <td>' . $value[3] . '</td>
                                        <td>' . $value[4] . '</td>
                                        <td>$ ' . $value[5] . '</td>
                                    </tr>';
                                            $i++;
                                        }
                                    }
                                    ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
                <script src="./assets/app.js"></script>
</body>

</html>