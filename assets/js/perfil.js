function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('avatarPreview');
                    var placeholder = document.getElementById('avatarPlaceholder');
                    if (placeholder) {
                        placeholder.classList.add('d-none');
                        img.classList.remove('d-none');
                    }
                    img.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }        let tipoVerificacionActual = null;

        function solicitarCodigoVerificacion(tipo) {
            tipoVerificacionActual = tipo;
            
            // Limpiar modal
            document.getElementById('modalMensaje').innerText = tipo === 'email' 
                ? 'Se enviará un código de verificación a tu correo electrónico.'
                : 'Se enviará un código de verificación a tu teléfono (via email).';
            document.getElementById('solicitudSpinner').style.display = 'none';
            document.getElementById('solicitudExito').style.display = 'none';
            document.getElementById('btnSolicitar').style.display = 'inline-block';
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('modalSolicitarCodigo'));
            modal.show();
        }

        function procesarSolicitud() {
            if (!tipoVerificacionActual) {
                console.error('tipoVerificacionActual no está definido');
                return;
            }
            
            console.log('📧 Iniciando solicitud de código para:', tipoVerificacionActual);
            
            document.getElementById('btnSolicitar').disabled = true;
            document.getElementById('solicitudSpinner').style.display = 'block';
            
            const formData = new FormData();
            formData.append('tipo', tipoVerificacionActual);
            
            console.log('📤 Enviando a: solicitar_codigo_verificacion.php');
            console.log('📋 Data:', { tipo: tipoVerificacionActual });
            
            fetch('solicitar_codigo_verificacion.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                console.log('📡 Respuesta recibida, status:', res.status);
                return res.text();
            })
            .then(text => {
                console.log('📄 Texto crudo:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('✅ JSON parseado:', data);
                    
                    if (data.ok) {
                        document.getElementById('solicitudSpinner').style.display = 'none';
                        document.getElementById('btnSolicitar').style.display = 'none';
                        document.getElementById('solicitudExito').style.display = 'block';
                        document.getElementById('mensajeSolicitud').innerText = data.message;
                        
                        console.log('🎉 Código enviado exitosamente');
                        
                        // Cambiar a modal de ingreso después de 2 segundos
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('modalSolicitarCodigo')).hide();
                            setTimeout(() => {
                                document.getElementById('inputCodigo').value = '';
                                document.getElementById('mensajeError').classList.add('d-none');
                                document.getElementById('btnVerificar').disabled = false;
                                const modalIngreso = new bootstrap.Modal(document.getElementById('modalIngresarCodigo'));
                                modalIngreso.show();
                                console.log('🔓 Modal de ingreso abierto');
                            }, 500);
                        }, 2000);
                    } else {
                        document.getElementById('solicitudSpinner').style.display = 'none';
                        document.getElementById('btnSolicitar').disabled = false;
                        console.error('❌ Error en respuesta:', data.error);
                        alert('Error: ' + data.error);
                    }
                } catch (e) {
                    console.error('❌ Error al parsear JSON:', e);
                    console.error('Text:', text);
                    alert('Error al procesar respuesta: ' + e.message);
                    document.getElementById('solicitudSpinner').style.display = 'none';
                    document.getElementById('btnSolicitar').disabled = false;
                }
            })
            .catch(err => {
                document.getElementById('solicitudSpinner').style.display = 'none';
                document.getElementById('btnSolicitar').disabled = false;
                console.error('❌ Error de conexión:', err);
                alert('Error de conexión: ' + err.message);
            });
        }

        function procesarVerificacion() {
            const codigo = document.getElementById('inputCodigo').value.trim();
            
            console.log('🔒 Verificando código:', codigo);
            
            if (codigo.length !== 6 || !codigo.match(/^\d+$/)) {
                document.getElementById('mensajeError').innerText = 'Por favor ingresa un código de 6 dígitos';
                document.getElementById('mensajeError').classList.remove('d-none');
                console.error('❌ Código inválido:', codigo);
                return;
            }
            
            document.getElementById('btnVerificar').disabled = true;
            document.getElementById('verificacionSpinner').style.display = 'block';
            
            const formData = new FormData();
            formData.append('codigo', codigo);
            
            console.log('📤 Enviando a: verificar_codigo.php');
            console.log('📋 Data:', { codigo: codigo });
            
            fetch('verificar_codigo.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                console.log('📡 Respuesta recibida, status:', res.status);
                return res.text();
            })
            .then(text => {
                console.log('📄 Texto crudo:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('✅ JSON parseado:', data);
                    
                    document.getElementById('verificacionSpinner').style.display = 'none';
                    
                    if (data.ok) {
                        document.getElementById('mensajeError').classList.add('d-none');
                        bootstrap.Modal.getInstance(document.getElementById('modalIngresarCodigo')).hide();
                        
                        console.log('🎉 Verificación exitosa');
                        alert(data.message);
                        location.reload();
                    } else {
                        document.getElementById('btnVerificar').disabled = false;
                        document.getElementById('mensajeError').innerText = data.error;
                        document.getElementById('mensajeError').classList.remove('d-none');
                        console.error('❌ Error en verificación:', data.error);
                    }
                } catch (e) {
                    console.error('❌ Error al parsear JSON:', e);
                    console.error('Text:', text);
                    document.getElementById('verificacionSpinner').style.display = 'none';
                    document.getElementById('btnVerificar').disabled = false;
                    document.getElementById('mensajeError').innerText = 'Error al procesar respuesta: ' + e.message;
                    document.getElementById('mensajeError').classList.remove('d-none');
                }
            })
            .catch(err => {
                document.getElementById('verificacionSpinner').style.display = 'none';
                document.getElementById('btnVerificar').disabled = false;
                document.getElementById('mensajeError').innerText = 'Error de conexión: ' + err.message;
                document.getElementById('mensajeError').classList.remove('d-none');
                console.error('❌ Error de conexión:', err);
            });
        }

        // Permitir Enter en el input de código
        document.addEventListener('DOMContentLoaded', function() {
            const inputCodigo = document.getElementById('inputCodigo');
            if (inputCodigo) {
                inputCodigo.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        procesarVerificacion();
                    }
                });
                // Permitir solo números
                inputCodigo.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
