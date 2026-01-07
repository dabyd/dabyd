// ============================================
// FORMULARIO DE CONTACTO - VALIDACIÓN Y AJAX
// ============================================

(function() {
    'use strict';
    
    // Prevenir ejecución múltiple
    if (window.ikgContactFormInitialized) {
        return;
    }
    window.ikgContactFormInitialized = true;

    /**
     * Inicializa el formulario de contacto
     */
    function initContactForm() {
        const contactForm = document.getElementById('contact-form');
        if (!contactForm) return;

        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const privacyCheckbox = document.getElementById('privacy');
        
        // ============================================
        // CONFIGURACIÓN DE MENSAJES DE ERROR
        // ============================================
        const errorMessages = {
            required: 'Este campo es obligatorio',
            email: 'Introduce un e-mail válido',
            tel: 'Introduce un teléfono válido',
            selectInvalid: 'Elige una opción válida'
        };

        // ============================================
        // FUNCIONES DE VALIDACIÓN
        // ============================================
        
        /**
         * Valida formato de email
         */
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email.trim());
        }

        /**
         * Valida formato de teléfono (acepta formatos internacionales)
         * Mínimo 9 dígitos, puede contener +, espacios, guiones, paréntesis
         */
        function isValidPhone(phone) {
            // Eliminar todo excepto números
            const digitsOnly = phone.replace(/[^0-9]/g, '');
            // Debe tener entre 9 y 15 dígitos
            return digitsOnly.length >= 9 && digitsOnly.length <= 15;
        }

        /**
         * Valida si un select tiene una opción válida seleccionada
         */
        function isValidSelect(value) {
            const trimmed = value.trim();
            return trimmed !== '' && trimmed !== '-';
        }

        /**
         * Obtiene o crea el contenedor de error para un campo
         */
        function getOrCreateErrorContainer(field) {
            const formGroup = field.closest('.form-group');
            if (!formGroup) return null;

            let errorContainer = formGroup.querySelector('.field-error');
            if (!errorContainer) {
                errorContainer = document.createElement('span');
                errorContainer.className = 'field-error';
                
                // Insertar después del input/select/textarea pero antes del small (instrucciones)
                const small = formGroup.querySelector('small');
                if (small) {
                    formGroup.insertBefore(errorContainer, small);
                } else {
                    formGroup.appendChild(errorContainer);
                }
            }
            return errorContainer;
        }

        /**
         * Muestra error en un campo
         */
        function showFieldError(field, message) {
            const formGroup = field.closest('.form-group');
            if (!formGroup) return;

            field.classList.add('field-invalid');
            field.classList.remove('field-valid');

            const errorContainer = getOrCreateErrorContainer(field);
            if (errorContainer) {
                errorContainer.textContent = message;
                errorContainer.style.display = 'block';
            }
        }

        /**
         * Limpia error de un campo
         */
        function clearFieldError(field) {
            const formGroup = field.closest('.form-group');
            if (!formGroup) return;

            field.classList.remove('field-invalid');
            field.classList.add('field-valid');

            const errorContainer = formGroup.querySelector('.field-error');
            if (errorContainer) {
                errorContainer.textContent = '';
                errorContainer.style.display = 'none';
            }
        }

        /**
         * Valida un campo individual
         * @returns {boolean} true si es válido, false si hay error
         */
        function validateField(field) {
            const value = field.value;
            const type = field.type || field.tagName.toLowerCase();
            const isRequired = field.hasAttribute('required');

            // Campos ocultos no se validan visualmente
            if (type === 'hidden') return true;

            // Campo vacío
            if (isRequired && value.trim() === '') {
                showFieldError(field, errorMessages.required);
                return false;
            }

            // Si no es obligatorio y está vacío, es válido
            if (!isRequired && value.trim() === '') {
                clearFieldError(field);
                return true;
            }

            // Validación según tipo
            switch (type) {
                case 'email':
                    if (!isValidEmail(value)) {
                        showFieldError(field, errorMessages.email);
                        return false;
                    }
                    break;

                case 'tel':
                    if (!isValidPhone(value)) {
                        showFieldError(field, errorMessages.tel);
                        return false;
                    }
                    break;

                case 'select-one':
                case 'select':
                    if (isRequired && !isValidSelect(value)) {
                        showFieldError(field, errorMessages.selectInvalid);
                        return false;
                    }
                    break;
            }

            // Si llegamos aquí, el campo es válido
            clearFieldError(field);
            return true;
        }

        /**
         * Valida todo el formulario
         * @returns {boolean} true si todo es válido
         */
        function validateForm() {
            let isValid = true;

            // Obtener todos los campos validables (excluyendo hidden y nonce)
            const fields = contactForm.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea');
            
            fields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            // Validar privacy checkbox
            if (!privacyCheckbox.checked) {
                isValid = false;
            }

            return isValid;
        }

        /**
         * Actualiza el estado del botón de submit
         */
        function updateSubmitButton() {
            const fields = contactForm.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea');
            let allValid = true;

            // Verificar si todos los campos obligatorios están rellenos y son válidos
            fields.forEach(field => {
                const isRequired = field.hasAttribute('required');
                const type = field.type || field.tagName.toLowerCase();
                const value = field.value.trim();

                if (type === 'hidden') return;

                // Campo obligatorio vacío
                if (isRequired && value === '') {
                    allValid = false;
                    return;
                }

                // Validaciones específicas si tiene valor
                if (value !== '') {
                    switch (type) {
                        case 'email':
                            if (!isValidEmail(value)) allValid = false;
                            break;
                        case 'tel':
                            if (!isValidPhone(value)) allValid = false;
                            break;
                        case 'select-one':
                        case 'select':
                            if (isRequired && !isValidSelect(value)) allValid = false;
                            break;
                    }
                }
            });

            // Verificar privacy checkbox
            if (!privacyCheckbox.checked) {
                allValid = false;
            }

            // Actualizar estado del botón
            submitBtn.disabled = !allValid;
        }

        // ============================================
        // EVENT LISTENERS
        // ============================================

        // Validar campo al perder el foco (blur)
        const validatableFields = contactForm.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea');
        
        validatableFields.forEach(field => {
            // Validar al perder foco
            field.addEventListener('blur', () => {
                validateField(field);
                updateSubmitButton();
            });

            // Actualizar botón mientras escribe (para ser más responsivo)
            field.addEventListener('input', () => {
                // Si ya tiene clase de error y ahora es válido, limpiar
                if (field.classList.contains('field-invalid')) {
                    validateField(field);
                }
                updateSubmitButton();
            });

            // Para selects, también validar al cambiar
            if (field.tagName.toLowerCase() === 'select') {
                field.addEventListener('change', () => {
                    validateField(field);
                    updateSubmitButton();
                });
            }
        });

        // Privacy checkbox
        privacyCheckbox.addEventListener('change', () => {
            updateSubmitButton();
        });

        // ============================================
        // INICIALIZACIÓN
        // ============================================
        
        // Deshabilitar botón inicialmente
        submitBtn.disabled = true;

        // ============================================
        // ENVÍO DEL FORMULARIO (AJAX)
        // ============================================
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Validar todo antes de enviar
            if (!validateForm()) {
                // Hacer scroll al primer error
                const firstError = contactForm.querySelector('.field-invalid');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            const messagesDiv = document.getElementById('form-messages');
            const originalBtnText = submitBtn.innerHTML;
            
            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Enviando...';
            if (messagesDiv) {
                messagesDiv.innerHTML = '';
                messagesDiv.className = 'form-messages';
            }
            
            try {
                const formData = new FormData(contactForm);
                formData.append('action', 'ikg_submit_form');
                
                // Verificar que ikigaiAjax existe
                if (typeof ikigaiAjax === 'undefined') {
                    throw new Error('Error de configuración. Recarga la página.');
                }
                
                const response = await fetch(ikigaiAjax.ajaxurl, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (messagesDiv) {
                        messagesDiv.innerHTML = result.data.message;
                        messagesDiv.classList.add('success');
                    }
                    if (typeof showMessage === 'function') {
                        showMessage(result.data.message, 'success');
                    }
                    contactForm.reset();
                    
                    // Limpiar estados de validación
                    validatableFields.forEach(field => {
                        field.classList.remove('field-valid', 'field-invalid');
                        const errorContainer = field.closest('.form-group')?.querySelector('.field-error');
                        if (errorContainer) {
                            errorContainer.style.display = 'none';
                        }
                    });
                    
                    // Deshabilitar botón después del reset
                    submitBtn.disabled = true;
                } else {
                    if (messagesDiv) {
                        messagesDiv.innerHTML = result.data.message;
                        messagesDiv.classList.add('error');
                    }
                    if (typeof showMessage === 'function') {
                        showMessage(result.data.message, 'error');
                    }
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Form submission error:', error);
                const errorMsg = error.message || 'Error de conexión. Inténtalo de nuevo.';
                if (messagesDiv) {
                    messagesDiv.innerHTML = errorMsg;
                    messagesDiv.classList.add('error');
                }
                if (typeof showMessage === 'function') {
                    showMessage(errorMsg, 'error');
                }
                submitBtn.disabled = false;
            } finally {
                submitBtn.innerHTML = originalBtnText;
            }
        });

        console.log('🎨 Formulari amb validació actiu');
    }

    // ============================================
    // INICIALIZACIÓN AL CARGAR EL DOM
    // ============================================
    
    if (document.readyState === 'loading') {
        // DOM aún cargando
        document.addEventListener('DOMContentLoaded', initContactForm);
    } else {
        // DOM ya cargado (por si el script se carga tarde)
        initContactForm();
    }
})();