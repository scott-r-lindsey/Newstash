import React from "react";
import { Route } from "react-router-dom";
import classNames from 'classnames';

import { withStyles, MuiThemeProvider, createMuiTheme } from '@material-ui/core/styles';
import AppBar from '@material-ui/core/AppBar';
import CssBaseline from '@material-ui/core/CssBaseline';
import IconButton from '@material-ui/core/IconButton';
import MenuIcon from '@material-ui/icons/Menu';
import Toolbar from '@material-ui/core/Toolbar';
import Typography from '@material-ui/core/Typography';
import SearchIcon from '@material-ui/icons/Search';
import InputBase from '@material-ui/core/InputBase';
import { ApolloProvider } from 'react-apollo';
import client from "../components/apolloclient";

import theme from "./theme";
import layout from '../../../css/mobile/layout.scss';

import SearchAuthor from "../components/Pages/SearchAuthor";
import Autocomplete from "../components/Trim/Autocomplete";
import About from "../components/Pages/About";
import BackToTop from "../components/Trim/BackToTop";
import Blog from "../components/Pages/Blog";
import Drawer from "../components/Trim/Drawer";
import Home from "../components/Pages/Home";
import Post from "../components/Pages/Post";
import Privacy from "../components/Pages/Privacy";
import SearchTitle from "../components/Pages/SearchTitle";
import Tos from "../components/Pages/Tos";
import Work from "../components/Pages/Work";

const styles = theme => ({
  appBarSpacer: theme.mixins.toolbar,

  grow: {
    flexGrow: 1,
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

    const { classes, appContext } = this.props;
    const initialProps = this.props.initialProps;

    return (
      <div>
        <ApolloProvider client={client}>
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
              <Route path="/blog" exact render={
                (props) => {
                  return (
                    <Blog {...props} initialProps={initialProps} />
                  );
                }}
              />
              <Route path="/blog/:id/:slug" exact render={
                (props) => {
                  return (
                    <Post {...props} initialProps={initialProps} />
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
        </ApolloProvider>
      </div>
    );
  }
}

export default withStyles(styles)(BooksApp);
