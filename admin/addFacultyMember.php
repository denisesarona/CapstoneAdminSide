<?php 
include('includes/header.php');
include('../functions/queries.php');
include('../middleware/adminMiddleware.php');
$positionresultSet = getData("positiontb");
$departmentresultSet = getData("departmenttb");
?>
<link rel="stylesheet" href="assets/css/style.css">

<!--------------- ADD FACULTY MEMBER PAGE --------------->

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-5">
                <h3>ADD FACULTY MEMBERS</h3>
                <div class="card-body">
                    <form action="codes.php" method="POST" enctype="multipart/form-data">
                        <div class="row" style="font-family: 'Poppins', sans-serif;">
                            <div class="col-md-6 mb-3"> 
                                <div class="form-group">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" placeholder="Enter Faculty Member's Name" name="name" id="name" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3"> 
                                <div class="form-group">
                                    <label for="position" class="form-label">Position</label>
                                    <select class="form-control" name="position" required>
                                        <option value="">Select Position</option>
                                        <?php
                                            while ($rows = $positionresultSet->fetch_assoc()) {
                                                $position_name = $rows['name'];
                                                echo "<option value='$position_name'>$position_name</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3"> 
                                <div class="form-group">
                                    <label for="department" class="form-label">Department</label>
                                    <select class="form-control" name="department" id="department" required onchange="updateDeptId()">
                                        <option value="">Select Department</option>
                                        <?php
                                            while ($rows = $departmentresultSet->fetch_assoc()) {
                                                $department_name = $rows['name'];
                                                $dept_id = $rows['dept_id'];
                                                // Set the option value to dept_id but display department name
                                                echo "<option value='$department_name' data-dept-id='$dept_id'>$department_name</option>";
                                            }
                                        ?>
                                    </select>
                                    <input type="hidden" name="dept_id" id="dept_id">
                                </div>
                            </div>
                            <div class="col-md-12 mb-3"> 
                                <div class="form-group">
                                    <label for="image" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" name="img" id="img" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn BlueBtn mt-2" name="addFaculty_button">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>  
        </div>
    </div>
</div>

<script>
function updateDeptId() {
    var departmentSelect = document.getElementById('department');
    var deptIdInput = document.getElementById('dept_id');
    var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
    deptIdInput.value = selectedOption.getAttribute('data-dept-id'); // Get the dept ID from data attribute
}
</script>

<!--------------- FOOTER --------------->

<?php include('includes/footer.php'); ?>
