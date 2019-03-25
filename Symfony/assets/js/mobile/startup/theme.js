
import { createMuiTheme } from '@material-ui/core/styles';
import * as Constants from '../constants'

const theme = createMuiTheme({
  typography: { useNextVariants: true },
  palette: {
    primary: {
      main: Constants.DarkGray,
    },
    background: {
      default: 'clear',
    },
    type:'dark',
  },
});

export default theme;
