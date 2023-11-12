
import moment from 'moment-timezone'
import 'moment/locale/ja';

moment.tz.setDefault('Asia/Tokyo');
moment.updateLocale('ja', {
  week: {
    dow : 1, // Monday is the first day of the week.
  }
});

export default moment