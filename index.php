<?php
include 'dbconnect.php';
session_start();

if (!$_SESSION["username_password"]) {
    header("Location: ./login.php");
} else {
    $username = explode("|", $_SESSION["username_password"])[0];
}

$sql_total_order = "SELECT COUNT(*) AS Total_Order FROM northwindmysql.orders a
                        JOIN northwindmysql.order_details b ON a.orderid = b.orderid
                        WHERE YEAR(a.orderdate) = 1995 and MONTH(a.orderdate) = 5";
$result_total_order = $conn->query($sql_total_order);

$sql_total_sales = "SELECT SUM((b.unitprice * b.quantity) * b.discount) AS Total_Sales FROM northwindmysql.orders a
                        JOIN northwindmysql.order_details b ON a.orderid = b.orderid
                        WHERE YEAR(a.orderdate) = 1995 AND MONTH(a.orderdate) = 5;";
$result_total_sales = $conn->query($sql_total_sales);

$sql_daily_sales = "SELECT DAY(a.orderdate) AS date, SUM(ROUND((b.unitprice * b.quantity) * b.discount, 2)) AS daily_sales FROM northwindmysql.orders a
                        JOIN northwindmysql.order_details b ON a.orderid = b.orderid
                        WHERE YEAR(a.orderdate) = 1995 AND MONTH(a.orderdate) = 5
                        GROUP BY a.orderdate
                        ORDER BY a.orderdate;";
$result_daily_sales = $conn->query($sql_daily_sales);

$sql_sales_by_product_categories = "SELECT d.categoryname AS category, SUM(ROUND((b.unitprice * b.quantity) * b.discount, 2)) AS sales FROM northwindmysql.orders a
                                        JOIN northwindmysql.order_details b ON a.orderid = b.orderid
                                        JOIN northwindmysql.products c ON b.productid = c.productid
                                        JOIN northwindmysql.categories d ON c.categoryid = d.categoryid
                                        WHERE YEAR(a.orderdate) = 1995 AND MONTH(a.orderdate) = 5
                                        GROUP BY d.categoryname;";
$result_sales_by_product_categories = $conn->query($sql_sales_by_product_categories);

$sql_sales_by_customers = "SELECT c.companyname AS company, SUM(ROUND((b.unitprice * b.quantity) * b.discount, 2)) AS sales FROM northwindmysql.orders a
                                JOIN northwindmysql.order_details b ON a.orderid = b.orderid
                                JOIN northwindmysql.customers c ON c.customerid = a.customerid
                                WHERE YEAR(a.orderdate) = 1995 AND MONTH(a.orderdate) = 5
                                GROUP BY c.companyname;";
$result_sales_by_customers = $conn->query($sql_sales_by_customers);

$sql_sales_by_employees = "SELECT CONCAT(c.firstname, ' ', c.lastname) AS employee, SUM(ROUND((b.unitprice * b.quantity) * b.discount, 2)) AS sales FROM northwindmysql.orders a
                                JOIN northwindmysql.order_details b ON a.orderid = b.orderid
                                JOIN northwindmysql.employees c ON c.employeeid = a.employeeid
                                WHERE YEAR(a.orderdate) = 1995 AND MONTH(a.orderdate) = 5
                                GROUP BY CONCAT(c.firstname, ' ', c.lastname);";
$result_sales_by_employees = $conn->query($sql_sales_by_employees);

if ($result_total_order->num_rows > 0) {
        // output data of each row
    $row_total_order = $result_total_order->fetch_assoc();
    $total_order = $row_total_order["Total_Order"];
} else {
    echo "0 results";
}

if ($result_total_sales->num_rows > 0) {
        // output data of each row
    $row_total_sales = $result_total_sales->fetch_assoc();
    $total_sales = $row_total_sales["Total_Sales"];
} else {
    echo "0 results";
}

if ($result_daily_sales->num_rows > 0) {
    // output data of each row
    $array_daily_sales = [];
    while ($row = $result_daily_sales->fetch_assoc()) {
        array_push($array_daily_sales, ["date" => $row["date"], "daily_sales" => $row["daily_sales"]]);
    }
} else {
    echo "0 results";
}

if ($result_sales_by_product_categories->num_rows > 0) {
    // output data of each row
    $array_sales_by_product_categories = [];
    while ($row = $result_sales_by_product_categories->fetch_assoc()) {
        array_push($array_sales_by_product_categories, ["category" => $row["category"], "sales" => $row["sales"]]);
    }
} else {
    echo "0 results";
}

if ($result_sales_by_customers->num_rows > 0) {
    // output data of each row
    $array_sales_by_customers = [];
    while ($row = $result_sales_by_customers->fetch_assoc()) {
        array_push($array_sales_by_customers, ["company" => $row["company"], "sales" => $row["sales"]]);
    }
} else {
    echo "0 results";
}

if ($result_sales_by_employees->num_rows > 0) {
    // output data of each row
    $array_sales_by_employees = [];
    while ($row = $result_sales_by_employees->fetch_assoc()) {
        array_push($array_sales_by_employees, ["employee" => $row["employee"], "sales" => $row["sales"]]);
    }
} else {
    echo "0 results";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">

    <!-- Amcharts -->
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

    <title>Kiddocare Assignment - Index</title>
</head>
<style>
    .chart {
        width: 100%;
        height: 500px;
    }
</style>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="/">Hi, <?php echo strtoupper($username); ?></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
        </ul>
        <form action="./logout.php" class="form-inline my-2 my-lg-0">
            <button class="btn btn-info my-2 my-sm-0" type="submit">Logout</button>
        </form>
    </div>
    </nav>
    <div class="container-fluid">
        <h1>Sales Dashboard</h1>
        <h3>May 1995</h3>

        <div class="row">
            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        Total Sales
                    </div>
                    <div class="card-body">
                        <h3 class="font-weight-bold">$ <?php echo number_format($total_sales, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        Total Orders
                    </div>
                    <div class="card-body">
                        <h3 class="font-weight-bold"><?php echo $total_order; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row pt-3">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">Daily Sales</div>
                            <!-- <div class="col-auto">May 1995</div> -->
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart" id="chartdailysales"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row pt-3 pb-3">
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">Sales by Product Categories</div>
                            <!-- <div class="col-auto">May 1995</div> -->
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart" id="chartsalesbyproductcategories"></div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">Sales by Customers</div>
                            <!-- <div class="col-auto">May 1995</div> -->
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart" id="chartsalesbycustomers"></div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">Sales by Employees</div>
                            <!-- <div class="col-auto">May 1995</div> -->
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart" id="chartsalesbyemployees"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>

    <script type="text/javascript">
        // Chart - Daily Sales
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create chart instance
            var chart = am4core.create("chartdailysales", am4charts.XYChart);

            // Add data
            chart.data = <?php echo json_encode($array_daily_sales); ?>;

            // Create axes

            var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "date";
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 30;

            categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
            if (target.dataItem && target.dataItem.index & 2 == 2) {
                return dy + 25;
            }
            return dy;
            });

            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

            // Create series
            var series = chart.series.push(new am4charts.ColumnSeries());
            series.dataFields.valueY = "daily_sales";
            series.dataFields.categoryX = "date";
            series.name = "Daily Sales";
            series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
            series.columns.template.fillOpacity = .8;

            var columnTemplate = series.columns.template;
            columnTemplate.strokeWidth = 2;
            columnTemplate.strokeOpacity = 1;

        });

        // Chart - Sales by Product Categories
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create chart instance
            var chart = am4core.create("chartsalesbyproductcategories", am4charts.PieChart);

            // Add and configure Series
            var pieSeries = chart.series.push(new am4charts.PieSeries());
            pieSeries.dataFields.value = "sales";
            pieSeries.dataFields.category = "category";

            // Let's cut a hole in our Pie chart the size of 30% the radius
            chart.innerRadius = am4core.percent(30);

            // Put a thick white border around each Slice
            pieSeries.slices.template.stroke = am4core.color("#fff");
            pieSeries.slices.template.strokeWidth = 2;
            pieSeries.slices.template.strokeOpacity = 1;
            pieSeries.slices.template
            // change the cursor on hover to make it apparent the object can be interacted with
            .cursorOverStyle = [
                {
                "property": "cursor",
                "value": "pointer"
                }
            ];

            pieSeries.alignLabels = false;
            pieSeries.labels.template.bent = true;
            pieSeries.labels.template.radius = 3;
            pieSeries.labels.template.text = "";
            pieSeries.labels.template.padding(0,0,0,0);

            pieSeries.ticks.template.disabled = true;

            // Create a base filter effect (as if it's not there) for the hover to return to
            var shadow = pieSeries.slices.template.filters.push(new am4core.DropShadowFilter);
            shadow.opacity = 0;

            // Create hover state
            var hoverState = pieSeries.slices.template.states.getKey("hover"); // normally we have to create the hover state, in this case it already exists

            // Slightly shift the shadow and make it more prominent on hover
            var hoverShadow = hoverState.filters.push(new am4core.DropShadowFilter);
            hoverShadow.opacity = 0.7;
            hoverShadow.blur = 5;

            // Add a legend
            chart.legend = new am4charts.Legend();

            chart.data = <?php echo json_encode($array_sales_by_product_categories); ?>;

        });

        // Chart - Sales by Customers
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            var chart = am4core.create("chartsalesbycustomers", am4charts.XYChart);
            
            chart.data = <?php echo json_encode($array_sales_by_customers); ?>;

            //create category axis for companies
            var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "company";
            categoryAxis.renderer.inversed = true;
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 10;

            //create value axis for income and expenses
            var valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.opposite = true;

            //create columns
            var series = chart.series.push(new am4charts.ColumnSeries());
            series.dataFields.categoryY = "company";
            series.dataFields.valueX = "sales";
            series.name = "Sales";
            series.columns.template.fillOpacity = 0.5;
            series.columns.template.strokeOpacity = 0;
            series.tooltipText = "Sales in {categoryY}: {valueX.value}";

            //add chart cursor
            chart.cursor = new am4charts.XYCursor();
            chart.cursor.behavior = "zoomY";

            //add legend
            chart.legend = new am4charts.Legend();

        });

        // Chart - Sales by Employees
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            var chart = am4core.create("chartsalesbyemployees", am4charts.XYChart);

            chart.data = <?php echo json_encode($array_sales_by_employees); ?>;

            //create category axis for companies
            var categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "employee";
            categoryAxis.renderer.inversed = true;
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 10;

            //create value axis for income and expenses
            var valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
            valueAxis.renderer.opposite = true;

            //create columns
            var series = chart.series.push(new am4charts.ColumnSeries());
            series.dataFields.categoryY = "employee";
            series.dataFields.valueX = "sales";
            series.name = "Sales";
            series.columns.template.fillOpacity = 0.5;
            series.columns.template.strokeOpacity = 0;
            series.tooltipText = "Sales in {categoryY}: {valueX.value}";

            //add chart cursor
            chart.cursor = new am4charts.XYCursor();
            chart.cursor.behavior = "zoomY";

            //add legend
            chart.legend = new am4charts.Legend();

        });
    </script>
</body>
</html>