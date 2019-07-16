(function(){
    window.ToastApi = {
        element_id: 'toasts-container',
        default_toast_lifespan: 5000,

        ToastType: {
            ERROR: 'error'
        },

        _generateToastId: function(){
            if( !this._idcounter ){
                this._idcounter = 1;
            }

            return 'toast_' + this._idcounter++;
        },

        _getToastContainer: function(){
            return document.getElementById( this.element_id );
        },

        _getToastType: function(type){
            if( !type ) return null;

            switch( type.toLowerCase() ){
                case 'error':
                    return type;
                default:
                    console.warn("Invalid toast type:", type);
                    return null;
            }
        },

        _add: function(toast){
            this._getToastContainer().appendChild( toast );
        },

        _remove: function(toast){
            this._getToastContainer().removeChild( toast );
        },

        _animate_in_and_add: function( toast, duration ){
            let _duration = duration || 500;
            ToastApi._add(toast);
            $(toast).fadeIn(_duration);
        },

        _animate_out_and_remove: function( toast, duration ){
            let _duration = duration || 500;
            $(toast).fadeOut(_duration, function(){
                ToastApi._remove(toast);
            });
        },

        toast: function( message, type, lifespan ){
            // Create toast
            let toast = document.createElement("span");
            toast.classList.add('toast');
            toast.id = this._generateToastId();

            // Add extra type, if provided & valid
            _type = this._getToastType(type);
            if(_type){
                toast.classList.add( _type );
            }

            // Allow toast to be dismissed
            toast.classList.add('dismissable');
            toast.onclick = function(){
                // Unset click
                toast.onclick = undefined;

                // Fast fade-out
                ToastApi._animate_out_and_remove(toast, 100);
            };

            // Set message text
            toast.innerText = message;

            // Add to container
            ToastApi._animate_in_and_add(toast);

            // Add timer to remove
            let _lifespan = lifespan || this.default_toast_lifespan;
            if( _lifespan > 0 ){
                setTimeout(
                    function(){
                        ToastApi._animate_out_and_remove(toast);
                    },
                    _lifespan
                );
            }
            // else this toast will persist until dismissed

            return toast;
        },

        getToast: function( toastId ){
            if( !toastId ){
                return undefined;
            }

            let _id = toastId.startsWith('#') ? toastId : '#' + toastId;
            return this._getToastContainer().querySelector( _id );
        },

        dismissToast: function ( toast_id ){
            let toast = this.getToast( toast_id );

            if( toast ){
                ToastApi._animate_out_and_remove(toast);
            }
        }
    };
})();
