<div data-role="page" id="accueil">
    <div role="main" class="ui-content">
        <h1>Accueil</h1>
    </div>

    <?php
        $sql = "Select * FROM preuve;";
        $preuves = tableSQL($sql);

        echo "<ul>";
        foreach ($preuves as $preuves) 
        {
            echo "<li>". $preuves->intitule." | ".$preuves->date." | ".$preuves->prix."€ | ". "</li>";
        }
        echo "<ul>";


























    ?>


    <?php include('includes/footer.php'); ?>
</div>