<div data-role="page" id="preuve">
    <div role="main" class="ui-content">
        <form method="POST" action="/preuve">
            <h2>Rajouter une preuve de dépense: </h2>

            <label for="intitule">Intitulé:</label>
            <input name="intitule" id="intitule" value="" type="text">

            <label for="date">Date de dépense:</label>
            <input name="date" id="date" value="" type="date">

            <label for="depense">Dépense:</label>
            <input name="depense" pattern="[0-9]*" id="depense" type="number" placeholder="Prix en €" >
        
            <label for="photo">Preuve photo:</label>
            <input name="photo" id="photo" value="" type="file">

            <button type="submit">Enregistrer</button>
        </form>
    </div>

    <?php include('includes/footer.php'); ?>
</div>