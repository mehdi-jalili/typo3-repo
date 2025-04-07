import DocumentService from "@typo3/core/document-service.js";
import DateTimePicker from "@typo3/backend/date-time-picker.js";

class ViewStatisticsBackend {
    initialize() {
        DocumentService.ready().then(() => {
            document.querySelectorAll('.t3js-datetimepicker').forEach(
                (e => DateTimePicker.initialize(e))
            )
        });
    }
}

export default new ViewStatisticsBackend();
