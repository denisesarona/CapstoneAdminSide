<?php
include('includes/header.php'); // Include header or any other necessary files
include('../functions/queries.php');
include('../middleware/adminMiddleware.php');

// Check if a department ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Capture the ID from the URL

    // Fetch department details
    $dept = getDepartmentsByID('departmenttb', $id);
    
    if ($dept && mysqli_num_rows($dept) > 0) {
        $data = mysqli_fetch_array($dept);
        $dept_name = htmlspecialchars($data['name']);
        $dept_id = intval($data['dept_id']); // Assuming 'id' is the column for department ID

        // Fetch faculty nodes based on department ID
        $nodes = getFacultyByDepartment($con, $dept_id); // Pass the correct department ID
    } else {
        $dept_name = "Department not found.";
        $nodes = []; // Initialize nodes as an empty array
    }
}


// Handle saving the updated node positions in the backend
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updated_nodes'])) {
    $updated_nodes = json_decode($_POST['updated_nodes'], true);

    // Check if decoding was successful and $updated_nodes is an array
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($updated_nodes)) {
        $_SESSION['error'] = "Error processing updated node data.";
    } else {
        foreach ($updated_nodes as $node) {
            if (isset($node['id']) && isset($node['pid'])) {
                $node_id = intval($node['id']);
                $parent_id = intval($node['pid']);

                // Prepare the SQL statement
                $update_sql = "UPDATE facultytb SET pid = ? WHERE faculty_id = ?";
                $stmt = $con->prepare($update_sql);
                
                // Check if preparation was successful
                if ($stmt === false) {
                    $_SESSION['error'] = "Error preparing statement: " . $con->error;
                    continue; // Skip to the next node
                }
        
                $stmt->bind_param("ii", $parent_id, $node_id);
                
                // Execute the statement
                if (!$stmt->execute()) {
                    $_SESSION['error'] = "Error executing statement: " . $stmt->error;
                }
        
                $stmt->close();
            }
        }

        $_SESSION['success'] = "Node positions updated successfully!";
    }
}
?>

<link rel="stylesheet" href="assets/css/orgChart.css">
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-5">
                <h3 class="text-center"><?php echo $dept_name; ?></h3>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="row mb-3"> 
                        <div class="col-md-6 mt-4"> 
                            <div class="form-group">
                                <label for="nodeId">Node ID:</label>
                                <input type="text" class="form-control" id="nodeId" name="nodeId" required>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4"> 
                            <div class="form-group">
                                <label for="pid">Parent ID (Node it is connected to):</label>
                                <input type="text" class="form-control" id="pid" name="pid" required>
                            </div>
                            <input type="hidden" name="updated_nodes" id="updated_nodes">
                        </div>
                        <div class="col-md-2 d-flex align-items-end mt-3"> 
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-success btn-block" name="save_changes">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="tree" class="mt-4" style="overflow-x: auto;"> <!-- Allow horizontal scrolling -->
                </div>
            </div>
        </div>   
    </div>
</div>

<script>
let nodes = <?php echo json_encode($nodes); ?>; // Convert PHP array to JSON

OrgChart.LINK_ROUNDED_CORNERS = 10;
// Define the template first
OrgChart.templates.myTemplate = OrgChart.templates.olivia;

// Then set the fields
OrgChart.templates.myTemplate.size = [350, 120];
OrgChart.templates.myTemplate.field_0 = 
    `<text style="font-size: 14px;" font-weight="bold" fill="#FFFFFFFF" x="100" y="60" text-anchor="right">{val}</text>`;

OrgChart.templates.myTemplate.field_1 = 
    `<text style="font-size: 12px;" fill="#FFFFFFFF" x="100" y="80" text-anchor="right">{val}</text>`;
    
OrgChart.templates.myTemplate.field_2 = 
    `<text style="font-size: 12px;" fill="#FFFFFFFF" x="280" y="20" text-anchor="right">Node ID {val}</text>`;

// OrgChart configuration
var chart = new OrgChart(document.getElementById("tree"), {
    template: "olivia",
    layout: OrgChart.tree,    
    enableDragDrop: false, // Disable drag-and-drop
    enableSearch: false,
    mouseScrool: OrgChart.none,
    align: OrgChart.ORIENTATION,
    scaleInitial: OrgChart.match.boundary,
    nodeMouseClick: OrgChart.action.edit,
    toolbar: {
        layout: false,
        zoom: true,
        fit: false,
        expandAll: false
    },
    nodeBinding: {
        field_0: "name",
        field_1: "position",
        field_2: "id",  
        img_0: "img",      
    },
    editForm: false,
    nodes: nodes  // Use the data retrieved from the database
});

// Function to gather updated node data before form submission
function gatherUpdatedNodeData() {
    let updatedNodes = [];
    
    // Get the node ID and new pid, ppid from the form
    let nodeId = document.getElementById('nodeId').value;
    let parentId = document.getElementById('pid').value;

    updatedNodes.push({
        id: nodeId,
        pid: parentId
    });

    document.getElementById('updated_nodes').value = JSON.stringify(updatedNodes);
}

// Add an event listener to the form submission
document.querySelector('form').addEventListener('submit', function(event) {
    gatherUpdatedNodeData(); // Gather updated data
});
</script>

<?php include('includes/footer.php'); ?>