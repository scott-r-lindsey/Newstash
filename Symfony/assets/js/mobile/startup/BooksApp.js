import React from "react";
import { Route } from "react-router-dom";
import classNames from 'classnames';

import Autocomplete from "../components/Trim/Autocomplete";
import Drawer from "../components/Trim/Drawer";
import BackToTop from "../components/Trim/BackToTop";

import About from "../components/Pages/About";
import Home from "../components/Pages/Home";
import Privacy from "../components/Pages/Privacy";
import SearchAuthor from "../components/Pages/SearchAuthor";
import SearchTitle from "../components/Pages/SearchTitle";
import Tos from "../components/Pages/Tos";
import Work from "../components/Pages/Work";

import { withStyles, MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import CssBaseline from '@material-ui/core/CssBaseline';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import SearchIcon from '@material-ui/icons/Search';

import InputBase from '@material-ui/core/InputBase';


import theme from "./theme";
import layout from '../../../css/mobile/layout.scss';


const styles = theme => ({
  appBarSpacer: theme.mixins.toolbar,

  grow: {
    flexGrow: 1,
  },
  inputRoot: {
  },
  inputInput: {
  },
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

    const { classes, initialProps } = this.props;

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
              <Autocomplete />
            </Toolbar>

          </AppBar>


          <main className={classes.content}>
            <div className={classes.appBarSpacer} />

            <Route path="/" exact render={(props) => <Home {...props} initialProps={initialProps} />} />
            <Route path="/about" exact component={About} />
            <Route path="/privacy" exact component={Privacy} />
            <Route path="/tos" exact component={Tos} />
            <Route path="/search/books" exact render={
              (props) => {
                let query = props.location.search;
                return (
                  <SearchTitle {...props} query={query} key={query} initialProps={initialProps} />
                );
              }}
            />
            <Route path="/search/author" exact render={
              (props) => {
                let query = props.location.search;
                return (
                  <SearchAuthor {...props} query={query} key={query} initialProps={initialProps} />
                );
              }}
            />
            <Route path="/book/:id/:slug" exact render={
              (props) => {
                return (
                  <Work {...props} initialProps={initialProps} />
                );
              }}
            />

          </main>

        </MuiThemeProvider>
      </div>
    );
  }
}

export default withStyles(styles)(BooksApp);
