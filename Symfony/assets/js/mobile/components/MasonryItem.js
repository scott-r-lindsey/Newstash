
import React from 'react';
import PropTypes from 'prop-types';
import { Link } from "react-router-dom";
import { withStyles } from '@material-ui/core/styles';
import { fiveStars } from "../util.js";
import * as Constants from '../constants'
import Typography from '@material-ui/core/Typography';

const styles = theme => ({
  coverImg: {
    borderBottom: '1px solid #ccc',
  },
  itemDiv: {
    display: 'block',
    borderRadius: '5px',
    overflow: 'hidden',
    boxShadow: '1px 1px 14px rgba(50,50,50,.75)',
    backgroundColor: 'hsla(0,0%,93.3%,.95)',
    position: 'relative',
  },
  footer: {
    position:'relative',
    paddingLeft:'70px',
    paddingTop:'4px',
  },
  user: {
    color: '#666',
    fontSize: 'calc(5vw - 5px)',
    lineHeight: '31px',
    textTransform: 'uppercase',
    fontFamily: 'Titillium Web,sans-serif',
    whiteSpace: "nowrap",
  },
  stars: {
    color: Constants.FireBrick,
    fontSize: 'calc(5vw - 6px)',
  },
  avatarImg: {
    position: 'absolute',
    left: '10px',
    top: '5px',
    height: '50px',
    width: '50px',
    borderRadius: '2px',
  },
  reviewTitle: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
  },
  reviewTitleText: {
    fontSize: '6vw',
    display:'block',
    position:'absolute',
    left:'10px',
    right:'5px',
    bottom:'5px',
    color:'#666',
    whiteSpace: "nowrap",
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    '&::before': {
      content: "'\\201C'",
    },
    '&::after': {
      content: "'\\201D'",
    },
  },
});

class MasonryItem extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
    }
  }

  findImgFactor(width) {
    return this.props.width / width;
  }

  generateWorkLink(work) {
    return '/book/' + work.id + '/' + work.slug;
  }


  render() {

    const { classes, item } = this.props;

    let factor = null;

    if (item.work) {
      factor = this.findImgFactor(item.work.coverX);
    }
    console.log(item);

    switch(item.type) {
      case 'rating':
        return (
          <div
            key={item.sig}
            className={classes.itemDiv}
            style={{
              width: `${this.props.width}px`,
              height: ((item.work.coverY * factor) + 70) + 'px',
            }}>
            <Link to={this.generateWorkLink(item.work)} title={item.work.title}>
              <img
                className={classes.coverImg}
                src={item.work.cover}
                height={item.work.coverY * factor}
                width={this.props.width} />
              {item.work.height}
            </Link>
            <div className={classes.footer}>
              <img src={item.user.avatarUrl80} className={classes.avatarImg} />
              <strong className={classes.user}>Rated:</strong><br />
              <span className={classes.stars}>{fiveStars(item.stars)}</span>
            </div>
          </div>
        );
      case 'post':
        return (
          <div
            key={item.sig}
            className={classes.itemDiv}
            style={{
              width: (this.props.width *2) + 'px',
              height: '100px',
            }}>
            This is a post
          </div>
        );
      case 'review':
        return (
          <div
            key={item.sig}
            className={classes.itemDiv}
            style={{
              width: `${this.props.width}px`,
              height: ((item.work.coverY * factor) + 100) + 'px',
            }}>
            <Link to={this.generateWorkLink(item.work)} title={item.work.title}>
              <img
                className={classes.coverImg}
                src={item.work.cover}
                height={item.work.coverY * factor}
                width={this.props.width} />
              {item.work.height}
            </Link>
            <div className={classes.footer}
              style={{height:'95px'}}
            >
              <img src={item.user.avatarUrl80} className={classes.avatarImg} />
              <strong className={classes.user}>Reviewed:</strong><br />
              <span className={classes.stars}>{fiveStars(item.stars)}</span><br />
              <div className={classes.reviewTitle}>
                  <em className={classes.reviewTitleText}>
                    {item.review.title}
                  </em>
              </div>
            </div>
          </div>
        );
      case 'work':
        return null;
      default:
        return null;
    }



    let height = 100;

    return (
      <div
        key={item.sig}
        style={{
          width: `${this.props.width}px`,
          height: `${height}px`,
          lineHeight: `${height}px`,
          color: 'white',
          fontSize: '32px',
          display: 'block',
          background: 'rgba(0,0,0,0.7)'
        }}>
        x
      </div>
    );
  }
}

MasonryItem.propTypes = {
  width: PropTypes.number.isRequired,
  item: PropTypes.object.isRequired,
};

export default withStyles(styles)(MasonryItem);

