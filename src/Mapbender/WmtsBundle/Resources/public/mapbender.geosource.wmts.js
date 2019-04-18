window.Mapbender = Mapbender || {};
window.Mapbender.WmtsSource = (function() {
    function WmtsSource(definition) {
        Mapbender.WmtsTmsBaseSource.apply(this, arguments);
    }
    WmtsSource.prototype = Object.create(Mapbender.WmtsTmsBaseSource.prototype);
    $.extend(WmtsSource.prototype, {
        constructor: WmtsSource,
        _initializeSingleCompatibleLayer: function(compatibleLayer, srsName) {
            var matrixSet = compatibleLayer.getMatrixSet();
            var options = $.extend(this._getNativeLayerOptions(matrixSet, compatibleLayer, srsName), {
                requestEncoding: 'REST',
                layer: compatibleLayer.options.identifier,
                name: compatibleLayer.options.title
            });
            var olLayer = new OpenLayers.Layer.WMTS(options);
            return olLayer;
        },
        _getNativeLayerOptions: function(matrixSet, compatibleLayer, srsName) {
            var parentValues = Mapbender.WmtsTmsBaseSource.prototype._getNativeLayerOptions.apply(this, arguments);
            var options = $.extend(parentValues, {
                matrixSet: matrixSet.identifier
            });
            options.matrixIds = matrixSet.tilematrices.map(function(matrix) {
                if (matrix.topLeftCorner) {
                    return $.extend({}, matrix, {
                        topLeftCorner: OpenLayers.LonLat.fromArray(matrix.topLeftCorner)
                    });
                } else {
                    return $.extend({}, matrix);
                }
            });
            return options;
        },
        /**
         * @param {WmtsTileMatrix} tileMatrix
         * @param {String} srsName
         * @return {Number}
         * @private
         */
        _getMatrixResolution: function(tileMatrix, srsName) {
            var engine = Mapbender.mapEngine;
            // OGC TileMatrix scaleDenom is calculated using meters, irrespective of projection units
            // OGC TileMatrix scaleDenom is also calculated assuming 0.28mm per pixel
            var metersPerUnit = 1.0 / engine.getProjectionUnitsPerMeter(srsName);
            var unitsPerPixel = 0.00028 / metersPerUnit;
            return tileMatrix.scaleDenominator * unitsPerPixel;
        },
        /**
         * @param {WmtsLayerConfig} layerDef
         * @return {string}
         */
        getPrintBaseUrl: function(layerDef) {
            var template = layerDef.options.tileUrls[0];
            return template
                .replace('{Style}', layerDef.options.style)
                // NOTE: casing of '{Style}' placeholder unspecified, emulate OpenLayers dual-casing support quirk
                .replace('{style}', layerDef.options.style)
                .replace('{TileMatrixSet}', layerDef.options.tilematrixset)
            ;
        }
    });
    Mapbender.Source.typeMap['wmts'] = WmtsSource;
    return WmtsSource;
}());
