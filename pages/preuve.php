<div data-role="page" id="preuve">
    <div role="main" class="ui-content">
        <form method="POST" action="/includes/create_preuve.php" enctype="multipart/form-data">
            <h2>Rajouter une preuve de dépense: </h2>

            <label for="intitule">Intitulé:</label>
            <input name="intitule" id="intitule" value="" type="text" placeholder="Ex: Plein d'essence...">

            <label for="date">Date de dépense:</label>
            <input name="date" id="date" value="" type="date">

            <label for="depense">Dépense:</label>
            <input name="depense" pattern="[0-9]*" id="depense" type="number" placeholder="Prix en €" >
        
            <label for="photo">Preuve photo:</label>
            <input name="photo" id="photo" type="file">

            <label for="description">Description (Facultatif):</label>
            <textarea cols="40" rows="8" name="description" id="description"></textarea>

            <button type="submit" class="ui-btn ui-btn-inline ui-icon-check ui-btn-icon-left">Enregistrer</button>
        </form>
    </div>

    <?php include('includes/footer.php'); ?>
</div>