
import React from 'react';
import { Link } from "react-router-dom";
import PropTypes from 'prop-types';
import * as Constants from '../../constants'
import Typography from '@material-ui/core/Typography';
import { withStyles } from '@material-ui/core/styles';

const styles = theme => ({
  link: {
    textDecoration: 'none',
  },
  postDiv: {
    margin: '0 auto 20px auto',
    borderRadius: '5px',
    overflow: 'hidden',
    boxShadow: '1px 1px 14px rgba(50,50,50,.75)',
    backgroundColor: 'hsla(0,0%,93.3%,.95)',
  },
  header: {
    color:'white',
    fontFamily: Constants.FancyFont,
    fontSize: '12vw',
    lineHeight: '16vw',
    textDecoration:'none',
    fontWeight: 700,
    textShadow: '2px 1px 2px #af1b14',
    paddingLeft:'10px',
    backgroundColor: Constants.FireBrick,
  },
  footer: {
    position:'relative',
    height:'65px',
    fontSize: 'calc(10vw - 10px)',
    fontFamily: Constants.BoringFont,
    textAlign:'right',
    marginRight: '15px',
    lineHeight:'29px',
    paddingLeft:'80px',
    color:'#222',
    textShadow: '2px 1px 2px #fff',
  },
  avatarImg: {
    position: 'absolute',
    left: '10px',
    top: '5px',
    height: '50px',
    width: '50px',
    borderRadius: '2px',
  },

});

class PinnedPost extends React.Component {

  findImgFactor(width) {
    return this.props.width / width;
  }

  generatePostLink(post) {
    return '/blog/' + post.id + '/' + post.slug;
  }

  render() {

    const { classes, item } = this.props;

    let factor = this.findImgFactor(item.post.imageX);

    return (
      <Link
        className={classes.link}
        to={this.generatePostLink(item.post)}
        title={item.post.title}>
        <div
          key={item.sig}
          className={classes.postDiv}
          style={{
            width: `${this.props.width}px`,
          }}>
          <div className={classes.header}>
            Books to Love
          </div>
          <img
            src={`/img/blog/${item.post.image}`}
            height={factor * item.post.imageY}
            width={this.props.width}
          />
          <div className={classes.footer}>
            <img src={item.user.avatarUrl80} className={classes.avatarImg} />
            <strong>{item.post.title}</strong>

          </div>
        </div>
      </Link>
    );
  }

}

PinnedPost.propTypes = {
  width: PropTypes.number.isRequired,
  item: PropTypes.object.isRequired,
};

export default withStyles(styles)(PinnedPost);

