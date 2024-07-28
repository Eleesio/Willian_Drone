$(document).ready(function() {
    // Função para selecionar/desmarcar todas as fotos
    $('#selectAll').click(function() {
        $('.photoCheckbox').prop('checked', this.checked);
    });

    // Filtrar fotos por nome e categoria
    $('#filterPhotoName, #filterCategory').on('input change', function() {
        const filterName = $('#filterPhotoName').val().toLowerCase();
        const filterCategory = $('#filterCategory').val().toLowerCase();

        $('#photosTable tbody tr').filter(function() {
            const photoName = $(this).find('td').eq(2).text().toLowerCase();
            const photoCategory = $(this).find('td').eq(3).text().toLowerCase();
            $(this).toggle(
                (filterName === '' || photoName.includes(filterName)) &&
                (filterCategory === '' || photoCategory.includes(filterCategory))
            );
        });
    });

    // Excluir fotos selecionadas
    $('#deleteSelectedPhotos').click(function() {
        $('.photoCheckbox:checked').closest('tr').remove();
    });

    // Exibir modal de envio de fotos selecionadas
    $('#sendSelectedPhotos').click(function() {
        const selectedPhotos = [];
        $('.photoCheckbox:checked').each(function() {
            const photoName = $(this).closest('tr').find('td').eq(2).text();
            selectedPhotos.push(photoName);
        });
        $('#selectedPhotosList').val(selectedPhotos.join('\n'));
    });

    // Submeter formulário de envio de fotos selecionadas
    $('#sendSelectedPhotosForm').submit(function(e) {
        e.preventDefault();
        const selectedClient = $('#selectClientForPhotos').val();
        const selectedPhotos = $('#selectedPhotosList').val().split('\n');
        alert(`Fotos (${selectedPhotos.join(', ')}) enviadas para o cliente: ${selectedClient}`);
    });

    // Submeter formulário de envio de fotos para cliente
    $('#sendPhotoToClientForm').submit(function(e) {
        e.preventDefault();
        const selectedClient = $('#selectClient').val();
        const selectedPhotos = $('#selectPhotos').val();
        alert(`Fotos (${selectedPhotos.join(', ')}) enviadas para o cliente: ${selectedClient}`);
    });
});

$(document).ready(function() {
    $('#selectAll').on('change', function() {
        $('.photo-select').prop('checked', this.checked);
    });

    $('.photo-select').on('change', function() {
        if ($('.photo-select:checked').length === $('.photo-select').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });
});