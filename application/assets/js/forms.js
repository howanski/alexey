import "./secured-button.js";
import { Datepicker } from "vanillajs-datepicker";
import pl from "./../../node_modules/vanillajs-datepicker/js/i18n/locales/pl.js";
Object.assign(Datepicker.locales, pl);

function initDatePicker(elem) {
    const datepicker = new Datepicker(elem, {
        format: elem.dataset.datepickerFormat,
        language: elem.dataset.datepickerLocale,
        todayBtn: true,
        weekStart: 1,
        disableTouchKeyboard: true,
        autohide: true,
    });
}

window.addEventListener("load", (event) => {
    let datePickerInputs = document.querySelectorAll(".datepicker-target");
    Array.from(datePickerInputs).map(initDatePicker);
});
