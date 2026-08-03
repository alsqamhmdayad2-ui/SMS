SMS.Utils.Form = {
    serialize: function(formElement) {
        const formData = new FormData(formElement);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        return data;
    },
    serializeRow: function(rowElement) {
        const inputs = rowElement.querySelectorAll('input, select, textarea');
        const data = {};
        inputs.forEach(input => {
            if (input.name || input.dataset.name) {
                const name = input.name || input.dataset.name;
                data[name] = input.value;
            }
        });
        return data;
    }
};
