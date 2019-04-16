
import React from 'react';
import PropTypes from 'prop-types';
import Typography from '@material-ui/core/Typography';
import { Link } from "react-router-dom";
import { withStyles } from '@material-ui/core/styles';

import * as Constants from '../../constants'
import { fiveStars, generatePostLink, generateWorkLink } from "../../util.js";

const styles = theme => ({
  coverImg: {
    borderBottom: '1px solid #ccc',
  },
  itemDiv: {
    display: 'block',
    position: 'relative',
  },
  itemWrapper: {
    position:'absolute',
    top: 0,
    bottom: 0,
    right:0,
    left: 0,
    borderRadius: '5px',
    overflow: 'hidden',
    boxShadow: '1px 1px 14px rgba(50,50,50,.75)',
    backgroundColor: 'hsla(0,0%,93.3%,.95)',
    transition: 'opacity .3s, transform .3s',
  },
  itemWrapperHidden: {
    transform: 'translate3d(0px, 100px, 0px)',
    opacity:'.1',
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
    fontFamily: Constants.BoringFont,
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
  postHead: {
    backgroundColor: Constants.FireBrick,
    color: 'white',
    fontFamily: Constants.FancyFont,
    fontSize: '9vw',
    fontWeight: 'bold',
    textShadow: '2px 1px 2px #af1b14',
    paddingLeft:'10px',
  },
  postFoot: {
    fontSize: '5vw',
    fontFamily: Constants.BoringFont,
    textAlign:'right',
    marginRight: '15px',
    lineHeight:'29px',
    color:'#222',
    textShadow: '2px 1px 2px #fff',
    whiteSpace: 'nowrap',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    paddingLeft:'10px',
  },
  link: {
    textDecoration: 'none',
  },
});

class MasonryItem extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
    }
  }

  get factor() {
    if (this.props.item.work) {
      return this.props.width / this.props.item.work.coverX;
    }
    else if (this.props.item.post) {
      return this.props.width / this.props.item.post.imageX;
    }
    else if (this.props.item.amzn_large_cover_x) {
      return this.props.width / this.props.item.amzn_large_cover_x;
    }
    return null;
  }

  componentDidMount() {
    const { classes } = this.props;

    this.props.addScrollableItem(
      this.layoutRef.current,
      () => { this.layoutRef.current.className = classes.itemWrapper }
    );
  }

  findItemHeight(item) {
    switch(item.type) {
      case 'rating':
        return ((item.work.coverY * this.factor) + 70) + 'px';
      case 'post':
        return ((item.post.imageY * this.factor) + 80) + 'px';
      case 'review':
        return ((item.work.coverY * this.factor) + 100) + 'px';
      default: // work
        return (this.props.item.amzn_large_cover_y * this.factor) + 'px';
    }
  }

  render() {

    const { classes, item } = this.props;
    this.layoutRef = React.createRef();

    return (
      <div
        key={item.sig ? item.sig : item.work_id}
        className={classes.itemDiv}
        style={{
          width: `${this.props.width}px`,
          height: this.findItemHeight(item),
        }}>
        <div
          className={classes.itemWrapper + ' ' + classes.itemWrapperHidden}
          ref={this.layoutRef}
        >
          { this.renderItem(item) }
        </div>
      </div>
    );
  }

  renderItem(item) {
    const { classes } = this.props;

    switch(item.type) {
      case 'rating':
        return (
          <Link to={generateWorkLink(item.work)} title={item.work.title}>
            <img
              className={classes.coverImg}
              src={item.work.cover}
              height={item.work.coverY * this.factor}
              width={this.props.width} />
            {item.work.height}
            <div className={classes.footer}>
              <img src={item.user.avatarUrl80} className={classes.avatarImg} />
              <strong className={classes.user}>Rated:</strong><br />
              <span className={classes.stars}>{fiveStars(item.stars)}</span>
            </div>
          </Link>
        );
      case 'post':
        return (

          <Link
            className={classes.link}
            to={generatePostLink(item.post)}
            title={item.post.title}>

            <div className={classes.postHead}>
              From the Blog
            </div>
            <img
              src={`/img/blog/${item.post.image}`}
              height={this.factor * item.post.imageY}
              width={this.props.width}
            />
            <div className={classes.postFoot}>
              { item.post.title }
            </div>
          </Link>

        );
      case 'review':
        return (
          <Link to={generateWorkLink(item.work)} title={item.work.title}>
            <img
              className={classes.coverImg}
              src={item.work.cover}
              height={item.work.coverY * this.factor}
              width={this.props.width} />
            {item.work.height}
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
          </Link>
        );
      default:
        return (
          <Link to={generateWorkLink(item)} title={item.title}>
            <img
              src={item.amzn_large_cover}
              width={item.amzn_large_cover_x * this.factor}
              height={item.amzn_large_cover_y * this.factor}
            />
          </Link>
        );
    }
  }
}

MasonryItem.propTypes = {
  addScrollableItem: PropTypes.func.isRequired,
  width: PropTypes.number.isRequired,
  item: PropTypes.object.isRequired,
};

export default withStyles(styles)(MasonryItem);
