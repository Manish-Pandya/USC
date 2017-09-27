var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var ibc;
(function (ibc) {
    var IBCEmailGen = /** @class */ (function (_super) {
        __extends(IBCEmailGen, _super);
        function IBCEmailGen() {
            return _super.call(this) || this;
        }
        IBCEmailGen.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        IBCEmailGen.urlMapping = new UrlMapping("getAllIBCEmails", "getIBCEmailGenById&id=", "saveIBCEmailGen");
        IBCEmailGen.contextMenuMacros = [{
                name: '[PI]',
                title: 'PI Name',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Protocol Title]',
                title: 'Protocol Title',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Protocol Number]',
                title: 'Protocol Number',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Protocol Approval Date]',
                title: 'Protocol Approval Date',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Expiration Date]',
                title: 'Expiration Date',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Reference Number]',
                title: 'Reference Number',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Review Assignment Name]',
                title: 'Review Assignment Name',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Review Assignment Due Date]',
                title: 'Review Assignment Due Date',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Meeting Date]',
                title: 'Meeting Date',
                fun: function () {
                    alert('dig');
                }
            }, {
                name: '[Location]',
                title: 'Location',
                fun: function () {
                    alert('dig');
                }
            }];
        return IBCEmailGen;
    }(FluxCompositerBase));
    ibc.IBCEmailGen = IBCEmailGen;
})(ibc || (ibc = {}));
