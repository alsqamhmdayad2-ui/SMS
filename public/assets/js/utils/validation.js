SMS.Utils.Validation = {
    isNumber: function(val) {
        return !isNaN(parseFloat(val)) && isFinite(val);
    },
    isValidMark: function(val, max) {
        if (!this.isNumber(val)) return false;
        const num = parseFloat(val);
        return num >= 0 && num <= parseFloat(max);
    },
    isValidPercentage: function(val) {
        if (!this.isNumber(val)) return false;
        const num = parseFloat(val);
        return num >= 0 && num <= 100;
    }
};
