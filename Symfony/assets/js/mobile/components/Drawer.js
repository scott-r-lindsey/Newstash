import React from "react";
import PropTypes from 'prop-types';
import { Link } from "react-router-dom";
import { withStyles } from '@material-ui/core/styles';

import Button from '@material-ui/core/Button';
import Collapse from '@material-ui/core/Collapse';
import ExpandLess from '@material-ui/icons/ExpandLess';
import ExpandMore from '@material-ui/icons/ExpandMore';
import IconButton from '@material-ui/core/IconButton';
import List from '@material-ui/core/List';
import ListItem from '@material-ui/core/ListItem';
import ListItemIcon from '@material-ui/core/ListItemIcon';
import ListItemText from '@material-ui/core/ListItemText';
import MenuIcon from '@material-ui/icons/Menu';
import SwipeableDrawer from '@material-ui/core/SwipeableDrawer';
import Typography from '@material-ui/core/Typography';

import Icon from '@material-ui/core/Icon';


import InboxIcon from '@material-ui/icons/MoveToInbox';

import * as Constants from '../constants'


const styles = theme => ({
  root: {
  },
  logo:{
    backgroundColor: Constants.FireBrick,
  },
  logoTypo: {
    fontSize:'12vw',
    lineHeight: 1.1,
  },
  logoInner:{
    color:'white',
    fontFamily: Constants.FancyFont,
    textDecoration:'none',
    fontWeight: 700,
    textShadow: '2px 1px 2px #323232',
    marginBottom: '-4px',
  },
});

class Drawer extends React.Component {

  constructor(props, context) {
    super(props, context);
  }

  state = {
    info: false,
  };

  toggleItem = (item) => () => {
    this.setState({
      [item]: (!this.state[item]),
    });
  };

  render() {

    const { classes } = this.props;

    // server / client safe iOS detection
    const iOS = typeof navigator != 'undefined' &&
      process.browser &&
      /iPad|iPhone|iPod/.test(navigator.userAgent);

    return(
      <SwipeableDrawer
        className={classes.root}
        open={this.props.open}
        onClose={this.props.toggle(false)}
        onOpen={this.props.toggle(true)}
        disableBackdropTransition={!iOS}
        disableDiscovery={iOS}
      >
        <List disablePadding={true}>
          <ListItem button key="Logo Item" className={classes.logo}>
              <Typography variant="h1" className={classes.logoTypo} >
                <Link to="/"
                  onClick={this.props.toggle(false)}
                  className={classes.logoInner} >
                  Books to Love
                </Link>
              </Typography>
          </ListItem>

          <ListItem button onClick={this.toggleItem('info')}>
            <ListItemText primary="Info" />
            {this.state.info ? <ExpandLess /> : <ExpandMore />}
          </ListItem>

          <Collapse in={this.state.info} timeout="auto" unmountOnExit>
            <List component="div" disablePadding>
              <ListItem button>
                <ListItemText primary="Blog" />
              </ListItem>
              <ListItem button>
                <ListItemText primary="About" />
              </ListItem>
              <ListItem button>
                <ListItemText primary="TOS" />
              </ListItem>
              <ListItem button>
                <ListItemText primary="Privacy" />
              </ListItem>
            </List>
          </Collapse>

        </List>

      </SwipeableDrawer>

    );
  }
}

Drawer.propTypes = {
  toggle: PropTypes.func.isRequired,
  open: PropTypes.bool.isRequired
};

export default withStyles(styles)(Drawer);
