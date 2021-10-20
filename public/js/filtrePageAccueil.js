window.addEventListener("DOMContentLoaded", (event) => {

    var orgaCheckbox = document.querySelector("input[name='orga']");
    var inscritCheckbox = document.querySelector('input[name="inscrit"]');
    var pasinscritCheckbox = document.querySelector('input[name="pas_inscrit"]');
    var passeCheckbox = document.querySelector('input[name="passe"]');

    var trSortiePasse = document.getElementsByClassName('passe');
    var trSortieOrga = document.getElementsByClassName('orga');
    var trSortiePasInscrit = document.getElementsByClassName('pas_inscrit');
    var trSortieInscrit = document.getElementsByClassName('inscrit');


    orgaCheckbox.addEventListener('change', function () {
        if (orgaCheckbox.checked) {
            for (var i=0;i<trSortieOrga.length;i+=1){
                trSortieOrga[i].style.display = 'table-row';
            }
        } else {
            for (i=0;i<trSortieOrga.length;i+=1){
                trSortieOrga[i].style.display = 'none';
            }
        }
    });

    inscritCheckbox.addEventListener('change', function () {
        if (inscritCheckbox.checked) {
            for (i=0;i<trSortieInscrit.length;i+=1){
                trSortieInscrit[i].style.display = 'table-row';
            }
        } else {
            for (i=0;i<trSortieInscrit.length;i+=1){
                trSortieInscrit[i].style.display = 'none';
            }
        }
    });

    pasinscritCheckbox.addEventListener('change', function () {
        if (pasinscritCheckbox.checked) {
            for (i=0;i<trSortiePasInscrit.length;i+=1){
                trSortiePasInscrit[i].style.display ='table-row';
            }
        } else {
            for (i=0;i<trSortiePasInscrit.length;i+=1){
                trSortiePasInscrit[i].style.display =  'none';
            }
        }
    });

    passeCheckbox.addEventListener('change', function () {
        if (!passeCheckbox.checked) {
            for (i=0;i<trSortiePasse.length;i+=1){
                trSortiePasse[i].style.display = 'none';
            }
        } else {
            for (i=0;i<trSortiePasse.length;i+=1){
                trSortiePasse[i].style.display = 'table-row';
            }
        }
    });
});
