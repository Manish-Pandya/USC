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
            // Unset click
            toast.onclick = undefined;

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
                if( toast.autoDismissTimer ){
                    // Remove timer since we're dismissing early
                    clearTimer( toast.autoDismissTimer );
                }

                // Fast fade-out
                ToastApi._animate_out_and_remove(toast, 100);
            };

            // Create toast content
            let content = document.createElement('span');
            content.classList.add('toast-content');

            // TODO: Cusom icons per-toast
            let typeicon = "";
            let messageHtml = "<span>" + message + "</span>";
            let dismissIconHtml = "<i class='icon-cancel-4'></i>";
            content.innerHTML = typeicon + messageHtml + dismissIconHtml;

            // Add toast content
            toast.appendChild(content);

            // Add toast to container
            ToastApi._animate_in_and_add(toast);

            // Add timer to remove
            let _lifespan = lifespan || this.default_toast_lifespan;
            if( _lifespan > 0 ){
                toast.autoDismissTimer = setTimeout(
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
