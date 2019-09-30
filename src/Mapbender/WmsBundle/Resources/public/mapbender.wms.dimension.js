var Mapbender = Mapbender || {};

Mapbender.Dimension = function(options) {
    if(options.type === 'interval' && options.name === 'time') {
        return new Mapbender.DimensionTime(options);
    } else if(options.type === 'interval') {
        return new Mapbender.DimensionScalar(options);
    } else if(options.type === 'multiple') {
        return new Mapbender.DimensionScalar(options); // Add MultipleScalar ???
    } else {
        return null;
    }
};

Mapbender.DimensionScalar = function(options, initDefault) {

    var min, max, value, hasDefaultOptions;
    this.default = null;

    if(Object.prototype.toString.call(options.extent) !== '[object Array]') {
        throw 'DimensionScalar extent option has to be type [object Array]:' + Object.prototype.toString.call(options.extent) + 'given'
    }

    if(options.extent.length < 2) {

        throw 'DimensionScalar extent option needs atleast two entries'
    }

    this.options = options;

    if(initDefault) {
        max = this.valueFromPart(1);
        min = this.valueFromPart(0);
        hasDefaultOptions = this.options.default !== null;
        value = !hasDefaultOptions ? options.extent[0] : options['default'];
        this.setDefault(min, max, value);
    }
};

Mapbender.DimensionScalar.Types = {
    INTERVAL: 'interval',
    MULTIPLE: 'multiple'
};

Mapbender.DimensionScalar.prototype.getOptions = function() {
    return this.options;
};

Mapbender.DimensionScalar.prototype.getDefault = function() {
    return this.default;
};

Mapbender.DimensionScalar.prototype.setDefault = function(defaults) {

    return this.default = defaults;

};

/**
 * @returns {int}
 */
Mapbender.DimensionScalar.prototype.getStep = function(value) {
    switch(this.options.type){
        case Mapbender.DimensionScalar.Types.INTERVAL:
            return Math.round(Math.abs(value - this.options.extent[0]) / this.options.extent[2]);
        case Mapbender.DimensionScalar.Types.MULTIPLE:
            return this.options.extent.indexOf(value);
    }
};

Mapbender.DimensionScalar.prototype.getStepsNum = function() {
    if (typeof this.stepsNum === 'undefined') {
        this.stepsNum = this.getStep(this.options.extent[1]);
    }
    return this.stepsNum;
};

Mapbender.DimensionScalar.prototype.partFromValue = function(val) {
    var valueStep = this.getStep(val);
    var endStep = this.getStep(this.options.extent[1]);
    if (endStep) {
        return valueStep / endStep;
    } else {
        return 0.0;
    }
};

Mapbender.DimensionScalar.prototype.stepFromPart =  function(part) {
    return Math.round(part * (this.getStepsNum()));
};

Mapbender.DimensionScalar.prototype.valueFromPart = function(part) {
    return this.valueFromStep(this.stepFromPart(part));
};
Mapbender.DimensionScalar.prototype.valueFromStep = function(step) {
    return this.options.extent[step];
};

Mapbender.DimensionScalar.prototype.valueFromStart = function() {
    return this.options.extent[0];
};

Mapbender.DimensionScalar.prototype.valueFromEnd = function() {
    return this.options.extent[this.options.extent.length - 1];
};
Mapbender.DimensionScalar.prototype.innerJoin = function() {
    console.warn('DimensionScalar.innerJoin() is not implemented yet!');
    return null;
};

Mapbender.DimensionScalar.prototype.getInRange = function(min, max, value) {
    var minStep = this.getStep(min);
    var maxStep = this.getStep(max);
    var valueStep = this.getStep(value);
    var step = Math.max(minStep, Math.min(valueStep, maxStep));
    return this.valueFromStep(step);
};

Mapbender.DimensionTime = function DimensionTime(options) {
    if (options.extent) {
        options.extent = options.extent.map(function(x) {
            return '' + x;
        });
    }
    Mapbender.DimensionScalar.call(this, options, false);
    try {
        this.template = new Mapbender.DimensionTime.DateTemplate(options.extent[0]);
    } catch (e) {
        this.template = new Mapbender.DimensionTime.DateTemplate(options.extent[1]);
    }
    this.start = new Date(options.extent[0]);
    this.end = new Date(options.extent[1]);
    var invalidDate = new Date('invalid');
    if (this.start.toString() === invalidDate.toString()) {
        throw new Error("Invalid start date input " + options.extent[0]);
    }
    if (this.end.toString() === invalidDate.toString()) {
        throw new Error("Invalid end date input" + options.extent[1]);
    }
    this.step = new PeriodISO8601(options.extent[2]);
    if (this.start > this.end) {
        var swapTmp = this.end;
        this.end = this.start;
        this.start = swapTmp;
    }
    this.setDefault(this.getInRange(this.valueFromPart(0), this.valueFromPart(1), this.options['default'] || options.extent[0]));
};

Mapbender.DimensionTime.prototype = Object.create(Mapbender.DimensionScalar.prototype);
Mapbender.DimensionTime.prototype.constructor = Mapbender.DimensionTime;

Mapbender.DimensionTime.prototype.getStep = function(value) {
    var valueDate = new Date(value);
    switch (this.step.getType()) {
        case 'year':
        case 'month':
            var years = valueDate.getUTCFullYear() - this.start.getUTCFullYear();
            var months = valueDate.getUTCMonth() - this.start.getUTCMonth();
            return Math.floor((12 * years + months) / (12 * this.step.years + this.step.months));
        case 'date':
            var step = -1;
            var startFormatted = this.template.formatDate(this.start);
            do {
                ++step;
                valueDate.setFullYear(valueDate.getFullYear() - this.step.years);
                valueDate.setMonth(valueDate.getMonth() - this.step.months);
                valueDate.setDate(valueDate.getDate() - this.step.days);
            } while (this.template.formatDate(valueDate) >= startFormatted);
            return step;
        case 'msec':
            return Math.floor((valueDate - this.start) / this.step.asMsec());
        default:
            throw new Error("Unsupported DimensionTime type " + this.step.getType());
    }
};

Mapbender.DimensionTime.prototype.valueFromStep = function valueFromPart(step) {
    var dateOut = new Date(this.start.toISOString());
    switch (this.step.getType()) {
        case 'year':
        case 'month':
        case 'date':
            dateOut.setUTCFullYear(dateOut.getUTCFullYear() + step * this.step.years);
            dateOut.setUTCMonth(dateOut.getUTCMonth() + step * this.step.months);
            dateOut.setUTCDate(dateOut.getUTCDate() + step * this.step.days);
            break;
        case 'msec':
            dateOut.setTime(dateOut.getTime() + step * this.step.asMsec());
            break;
        default:
            throw new Error("Unsupported step type " + this.step.getType());
    }
    return this.template.formatDate(dateOut);
};

Mapbender.DimensionTime.prototype.valueFromStart = function valueFromStart() {
    return this.start.toJSON();
};

Mapbender.DimensionTime.prototype.valueFromEnd = function valueFromEnd() {
    return this.end.toJSON();
};

/**
 * @param {Mapbender.DimensionTime} another
 * @return {null}
 */
Mapbender.DimensionTime.prototype.innerJoin = function innerJoin(another) {
    if (!this.step.equals(another.step)) {
        return null;
    }
    var options = $.extend(true, {}, this.options);
    var startDimension, endDimension;
    if (this.start.getTime() >= another.start.getTime()) {
        startDimension = this;
    } else {
        startDimension = another;
    }
    if(this.end.getTime() >= another.end.getTime()) {
        endDimension = another;
    } else {
        endDimension = this;
    }
    var startStep = endDimension.getStep(this.template.formatDate(startDimension.start));
    if (endDimension.valueFromStep(startStep) !== this.template.formatDate(startDimension.start)) {
        console.warn("Dimension join failure", this, another);
        return null;
    }

    options.extent = [
        this.template.formatDate(startDimension.start),
        this.template.formatDate(endDimension.end),
        this.step.toString()
    ];
    options.origextent = options.extent.slice();
    options.current = this.options.current === another.options.current ? this.options.current : null;
    options.multipleValues = this.options.multipleValues && another.options.multipleValues;
    options.nearestValue = this.options.nearestValue && another.options.nearestValue;
    return Mapbender.Dimension(options);
};

Mapbender.DimensionTime.DateTemplate = function(value) {
    var dateTimeStr = '' + value;
    if(dateTimeStr.indexOf('-') === 0) {
        var date = new Date(value);
        console.warn("Ambiguous vcard date format, truncating ambiguously", dateTimeStr);
        dateTimeStr = date.toISOString().replace(/([-T:]00)*\.000(Z?)$/, '');
    }
    var dateTimeParts = dateTimeStr.split('T');
    var dateString = dateTimeParts[0];
    var timeString = dateTimeParts[1];
    if (dateString.indexOf(':') !== -1) {
        dateString = '';
        timeString = dateTimeParts[0];
    }
    this.ymd = dateString.split('-').map(function(part) {
        return part && !isNaN(parseInt(part));
    });
    this.hmsm = (timeString || '').replace(/Z$/, '').split(':').map(function(part) {
        return part && !isNaN(parseInt(part));
    });
    if ((timeString || '').indexOf('.') !== -1) {
        this.hmsm[2] = true;
        this.hmsm[3] = true;
    }
    var truths = this.ymd.concat(this.hmsm).filter(function(x) {
        return !!x;
    });
    if (!truths.length) {
        throw new Error("Invalid date template input " + value);
    }
};

Object.assign(Mapbender.DimensionTime.DateTemplate.prototype, {
    formatDate: function (date) {
        var value = date.toISOString().replace(/Z$/, '');
        if (!this.hmsm[0]) {
            // strip time portion entirely
            value = value.replace(/T.*$/, '');
        } else if (!this.hmsm[1]) {
            // strip minutes, seconds and milliseconds
            value = value.replace(/(T\d\d)(.*)$/, '$1');
        } else if (!this.hmsm[2]) {
            // strip seconds and milliseconds
            value = value.replace(/(T\d\d:\d\d)(.*)$/, '$1');
        } else if (!this.hmsm[3]) {
            // strip milliseconds
            value = value.replace(/(T\d\d:\d\d:\d\d)(.*)$/, '$1');
        }
        if (!this.ymd[0]) {
            // strip date portion entirely
            value = value.replace(/^[^T]*/, '');
        } else if (!this.ymd[1]) {
            // strip month and day, keep time portion
            value = value.replace(/^(\d\d\d\d)([^T]*)(.*)$/, '$1$3');
        } else if (!this.ymd[2]) {
            // strip day, keep time portion
            value = value.replace(/^(\d\d\d\d-\d\d)([^T]*)(.*)$/, '$1$3');
        }
        // if only time portion remains, strip leading T
        return value.replace(/^T/, '');
    }
});

PeriodISO8601 = function(datetimeStr) {
    var pattern = /^(?:P)(?:(\d+)(?:Y))?(?:(\d+)(?:M))?(?:(\d+)(?:D))?(?:T(?:(\d+)(?:H))?(?:(\d+)(?:M))?(?:(\d+)(?:S))?)?$/;
    if (!datetimeStr.match(pattern)) {
        throw new Error("Invalid duration input " + datetimeStr);
    }
    var parts = datetimeStr.split(pattern).map(function(part) {
        return parseInt(part) || 0;
    });
    this.years = parts[1];
    this.months = parts[2];
    this.days = parts[3];
    this.hours = parts[4];
    this.mins = parts[5];
    this.secs = parts[6];
};

Object.assign(PeriodISO8601.prototype, {
    getType: function() {
        if (!this.years && !this.months) {
            return 'msec';
        } else {
            if (!this.days && !this.hours && !this.mins && !this.secs) {
                if (this.years && !this.months) {
                    return 'year';
                } else {
                    return 'month';
                }
            } else {
                return 'date';
            }
        }
    },
    toString: function() {
        var time = this.hours > 0 ? this.hours + 'H' : '';
        time += this.mins > 0 ? this.mins + 'M' : '';
        time += this.secs > 0 ? this.secs + 'S' : '';
        time = time.length > 0 ? 'T' + time : '';
        var date = this.years > 0 ? this.years + 'Y' : '';
        date += this.months > 0 ? this.months + 'M' : '';
        date += this.days > 0 ? this.days + 'D' : '';
        return (date.length + time.length) > 0 ? 'P' + (date + time) : '';
    },
    equals: function(period) {
        return this.years === period.years && this.months === period.months && this.days === period.days && this.hours === period.hours && this.mins === period.mins && this.secs === period.secs;
    },
    asMsec: function() {
        return 1000 * (
            (this.secs || 0)
            + (this.mins || 0) * 60
            + (this.hours || 0) * 3600
            + (this.days || 0) * 86400
        );
    }
});
