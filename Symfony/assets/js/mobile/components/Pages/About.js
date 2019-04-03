import React from "react";
import { Link } from "react-router-dom";
import { Helmet } from "react-helmet";
import { withStyles } from '@material-ui/core/styles';

import * as Constants from '../../constants'

const styles = theme => ({
  cover: {
    paddingTop: '70px',
    backgroundColor:'white',
    position: 'absolute',
    top: '0',
    bottom: '0',
    left: '0',
    right: '0',
    backgroundImage: 'url(/img/photo/books_on_shelf_small.jpeg)',
    backgroundSize: 'cover',
  },
  link: {
    color:'black',
    margin: '10px 10px',
  },
  heart: {
    position: 'absolute',
    top:'calc(45% - 15vw)',
    left:'30vw',
    backgroundColor: Constants.FireBrick,
    height: '40vw',
    transform: 'rotate(-45deg)',
    width: '40vw',
    '&::before': {
      content: '""',
      backgroundColor: Constants.FireBrick,
      borderRadius: '50%',
      height: '40vw',
      position: 'absolute',
      width: '40vw',
      top: '-20vw',
      left: '0px',
    },
    '&::after': {
      content: '""',
      backgroundColor: Constants.FireBrick,
      borderRadius: '50%',
      height: '40vw',
      position: 'absolute',
      width: '40vw',
      left: '20vw',
    },

  },
  main: {
    position:'absolute',
    height: '50vw',
    top: 'calc(45% - 21vw)',
    left: '40%',
    display:'block',
    margin: '0 auto',
    width:'20vw',
    textAlign:'center',
  },
  fancy: {
    color: 'white',
    fontFamily: Constants.FancyFont,
    fontSize:'12vw',
    lineHeight:'13vw',
    fontWeight: 'bold',
    textShadow: '2px 1px 2px #af1b14',
  },
  footer: {
    backgroundColor:'white',
    position:'absolute',
    bottom: '0',
    left: '0',
    right: '0',
    width: '100%',
    fontFamily: Constants.BoringFont,
    textAlign:'center',
    fontSize:'7vw',
    lineHeight:'14vw',
  },
  copy: {
    fontSize:'6vw',
    lineHeight:'8vw',
    opacity: '.7',
  }
});

class About extends React.Component {

  render() {

    const { classes } = this.props;

    return (
      <div>
        <Helmet>
          <title>About Books to Love</title>
        </Helmet>
        <div className={classes.cover}>

          <div className={classes.heart} />
          <div className={classes.main} >
            <span className={classes.fancy}>
              Books to Love
            </span>
          </div>

          <div className={classes.footer}>
              <Link to="/privacy" className={classes.link}>Privacy</Link>
              <Link to="/tos" className={classes.link}>TOS</Link>
              <br />
              <em className={classes.copy}>
                Copyright &copy; { new Date().getFullYear() } Scott Lindsey
              </em>
          </div>
        </div>
      </div>
    );
  }
}

export default withStyles(styles)(About);

