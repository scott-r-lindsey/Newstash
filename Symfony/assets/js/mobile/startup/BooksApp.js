import React from "react";
import { Route } from "react-router-dom";
import classNames from 'classnames';

import Home from "../components/Home";
import Work from "../components/Work";
import Drawer from "../components/Drawer";

import { withStyles, MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import CssBaseline from '@material-ui/core/CssBaseline';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';

import theme from "./theme";
import layout from '../../../css/mobile/layout.scss';


const styles = theme => ({
  appBarSpacer: theme.mixins.toolbar,
});

class BooksApp extends React.Component {

  state = {
    drawer: false
  };

  toggleDrawer = (open) => () => {
    this.setState({
      drawer: open,
    });
  };

  render() {

    const { classes } = this.props;

    return (
      <div>
        <MuiThemeProvider theme={theme}>
          <CssBaseline />

          <Drawer toggle={this.toggleDrawer.bind()} open={this.state.drawer} />

          <AppBar
            position="absolute"
            className={classNames(classes.appBar, this.state.open && classes.appBarShift)}
          >
            <Toolbar disableGutters={!this.state.open} className={classes.toolbar}>
              <IconButton
                className={classes.menuButton}
                color="inherit"
                aria-label="Menu"
                onClick={this.toggleDrawer(true)}
              >
                <MenuIcon />
              </IconButton>
            </Toolbar>
          </AppBar>

          <main className={classes.content}>
            <div className={classes.appBarSpacer} />

            <Route path="/" exact component={Home} />
            <Route path="/book/:id/:slug" exact component={Work} />

          </main>
        </MuiThemeProvider>
      </div>
    );
  }
}

export default withStyles(styles)(BooksApp);
