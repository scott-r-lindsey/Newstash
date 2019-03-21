import React from "react";
import Home from "../components/Home";
import Work from "../components/Work";
import { Route } from "react-router-dom";

import classNames from 'classnames';
import { withStyles } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
//import Badge from '@material-ui/core/Badge';
//import ChevronLeftIcon from '@material-ui/icons/ChevronLeft';
import CssBaseline from '@material-ui/core/CssBaseline';
//import Divider from '@material-ui/core/Divider';
//import Drawer from '@material-ui/core/Drawer';
//import IconButton from '@material-ui/core/IconButton';
//import List from '@material-ui/core/List';
//import MenuIcon from '@material-ui/icons/Menu';
//import NotificationsIcon from '@material-ui/icons/Notifications';
//import SimpleLineChart from './SimpleLineChart';
//import SimpleTable from './SimpleTable';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';


const styles = theme => ({
  appBarSpacer: theme.mixins.toolbar,

});

class BooksApp extends React.Component {

  state = {
    open: true,
  };

  render() {

    const { classes } = this.props;

    return (
      <div>
        <CssBaseline />


        <AppBar
          position="absolute"
          className={classNames(classes.appBar, this.state.open && classes.appBarShift)}
        >
          <Toolbar disableGutters={!this.state.open} className={classes.toolbar}>
          </Toolbar>
        </AppBar>

        <main className={classes.content}>
          <div className={classes.appBarSpacer} />

          <Route path="/" exact component={Home} />
          <Route path="/book/:id/:slug" exact component={Work} />

        </main>
      </div>
    );
  }
}

export default withStyles(styles)(BooksApp);
