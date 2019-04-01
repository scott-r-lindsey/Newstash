import React from "react";
import PropTypes from 'prop-types';
import { withStyles } from '@material-ui/core/styles';
import Icon from '@material-ui/core/Icon';

import * as Constants from '../../constants'


const styles = theme => ({
  tab:{
    position: 'fixed',
    top: '80px',
    right: '-100px',
    width: '60px',
    height: '60px',
    borderRadius: '8px 0 0 8px ',
    backgroundColor: Constants.FireBrick,
    boxShadow: '1px 1px 14px rgba(50,50,50,.75)',
    color:'white',
    textAlign: 'center',
    fontSize: '18px',
    lineHeight: '16px',
    fontFamily: Constants.BoringFont,
    paddingTop: '8px',
    fontWeight: 'bold',
    transition: 'right .3s',
  },
  tabVisible: {
    right: '0px',
  }
});

class BackToTop extends React.Component {


  constructor(props, context) {
    super(props, context);
  }

  toTop = () => {
    window.scrollTo(0,0)
  }

  handleScroll = () => {

    const { classes } = this.props;

    if (window.scrollY > window.innerHeight) {
      this.layoutRef.current.className = classes.tab + ' ' + classes.tabVisible;
    }
    else{
      this.layoutRef.current.className = classes.tab;
    }
  }

  componentDidMount() {
    document.addEventListener("touchmove", this.handleScroll);
    document.addEventListener("scroll", this.handleScroll);
    document.addEventListener("orientationchange", this.handleScroll);
  }

  componentWillUnmount() {
    document.removeEventListener("touchmove", this.handleScroll);
    document.removeEventListener("scroll", this.handleScroll);
    document.removeEventListener("orientationchange", this.handleScroll);
  }

  render() {
    console.log('what up back to top');

    const { classes } = this.props;
    this.layoutRef = React.createRef();

    return(
      <div
        className={classes.tab}
        onClick={this.toTop}
        ref={this.layoutRef}
      >
        <Icon>arrow_upward</Icon><br />
        top
      </div>
    );
  }
}

BackToTop.propTypes = {
};

export default withStyles(styles)(BackToTop);
