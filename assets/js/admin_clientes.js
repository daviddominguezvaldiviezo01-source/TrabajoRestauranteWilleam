function editarCliente(id, nombre, email, telefono) {
    document.getElementById('edit_id_cliente').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_telefono').value = telefono;
    var modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
    modal.show();
}