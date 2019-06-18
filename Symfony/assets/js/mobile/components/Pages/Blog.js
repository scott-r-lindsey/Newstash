import PropTypes from 'prop-types';
import React from "react";
import Moment from 'react-moment';
import gql from "graphql-tag";
import { Helmet } from "react-helmet";
import { Link } from "react-router-dom";
import { Query } from "react-apollo";
import { withStyles } from '@material-ui/core/styles';
import Icon from '@material-ui/core/Icon';
import Copyright from "../Trim/Copyright";

import * as Constants from '../../constants'
import Loading from "../Trim/Loading";
import postsGql from 'raw-loader!../../raw/graphql/posts.graphql';
import { generatePostLink, generatePostImageLink } from "../../util.js";

const styles = theme => ({
  wrap: {
    padding: '3vw 2vw 0vw 2vw',
    backgroundColor:'#ffffff7a',
    minHeight: 'calc(100vh - 56px)',
    fontFamily: Constants.BoringFont,
  },

  // first post classes
  firstPost: {
    paddingTop: '3vw',
    textAlign: 'center',
    background:
      'radial-gradient(ellipse at 65% 50%, rgba(36, 63, 195, 0.4) 0, rgba(255, 255, 255, 0) 100%), ' +
      'linear-gradient(0deg, rgba(48,180,152,1) 0%, rgba(25,194,119,1) 100%)',
    fontSize: '8vw',
    lineHeight: '5vw',
    padding: '0 2vw',
    '& strong': {
      backgroundColor: '#ffffff5c',
      display: 'inline-block',
      borderRadius: '2vw',
      paddingTop: '3vw',
      fontSize: '10vw',
      fontFamily: Constants.DisplayFont,
      fontWeight: '600',
      color: 'white',
      lineHeight: '11vw',
      textShadow: '2px 2px 2px #187b64',
    },
  },
  firstPostLink: {
    textDecoration: 'none',
  },
  firstImage: {
    width:'100%',
    verticalAlign: 'bottom',
  },
  firstLead: {
    '& p': {
      margin: 0,
      fontSize: '5vw',
      lineHeight: '7vw',
      opacity: '.9',
      color: 'white',
      textShadow: '2px 2px 2px #187b64',
    },
    fontStyle: 'italic',
    maxHeight:'40vh',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
    display: '-webkit-box',
    WebkitLineClamp: '3',
    WebkitBoxOrient: 'vertical',
  },
  firstInfo: {
    color:'white',
    backgroundColor: '#4060825c',
    padding: '2vw',
    fontSize: '4.0vw',
    textAlign: 'left',
    margin: '0 -2vw',
    textShadow: '2px 2px 2px #187b64',
    '& em': {
      paddingLeft: '4vw',
    },
  },

  // all additiional posts
  post: {
    background:
      'radial-gradient(ellipse at 65% 50%, rgba(36, 63, 195, 0.4) 0, rgba(255, 255, 255, 0) 100%), ' +
      'linear-gradient(0deg, rgba(48,180,152,1) 0%, rgba(25,194,119,1) 100%)',
    marginTop: '3vw',
    textAlign: 'center',
    position: 'relative',
    overflow: 'hidden',
    padding:'0vw',
    '&:nth-of-type(2n)': {
      background:
        'radial-gradient(ellipse at 65% 50%, rgba(36, 195, 158, 0.4) 0, rgba(255, 255, 255, 0) 100%), ' +
        'linear-gradient(0deg, rgb(48, 119, 180) 0%, rgb(25, 69, 194) 100%)',
      textShadow: '2px 2px 2px #1f4c79',
    },
  },
  postLink: {
    textDecoration: 'none',
  },
  postImage: {
    position: 'absolute',
    width: '40vw',
    left: '0',
    top: '0',
    backgroundSize: 'cover',
    height:'100%',
  },
  postLead: {
    position:'relative',
    padding: '4vw 4vw 8vw 4vw',
    width:'56vw',
    fontSize: '8vw',
    lineHeight: '7vw',
    marginLeft:'40vw',
    color: 'white',
    textShadow: '2px 2px 2px #187b64',
    fontFamily: Constants.DisplayFont,
    fontWeight: '600',
  },
  postInfo: {
    position:'absolute',
    bottom: '0',
    right: '0',
    left: '0',
    backgroundColor: '#4060825c',
    fontFamily: Constants.BoringFont,
    fontSize: '4vw',
    lineHeight: '6vw',
  },
  readmore: {
    float: 'right',
    textDecoration: 'underline',
    fontWeight: '600',
    lineHeight: '7vw',
  },
});

class Blog extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
    };
  }

  renderPosts(posts) {
    const { classes } = this.props;

    return (
      <div className={classes.wrap}>

        {posts.edges.map((post, index) => (
          (index === 0) ? // first post
            <div key={post.node.id} >
              <Link to={generatePostLink(post.node)} className={classes.firstPostLink} >
                { (console.log(post.node), post.node.image) ?
                  <img
                    className={classes.firstImage}
                    src={generatePostImageLink(post.node)} /> : null
                }
                <div className={classes.firstPost}>
                  <strong>{post.node.title}</strong>
                  <div
                    className={classes.firstLead}
                    dangerouslySetInnerHTML={{__html: post.node.lead.trim()}} />
                  <div className={classes.firstInfo}>
                    <Icon style={{verticalAlign: 'middle'}}>perm_identity</Icon>{post.node.user.first_name}&nbsp;
                    <em>
                      <Moment interval={30000} fromNow ago>
                        {post.node.published_at}
                      </Moment> Ago
                    </em>
                    <div className={classes.readmore}>
                      Read More...
                    </div>
                  </div>
                </div>
              </Link>
            </div>
           : // all other posts
            <div key={post.node.id} className={classes.post} >
              <Link to={generatePostLink(post.node)} className={classes.postLink} >
                { (console.log(post.node), post.node.image) ?
                  <div
                    className={classes.postImage}
                    style={{backgroundImage: 'url(' + generatePostImageLink(post.node) + ')'}} /> : null
                }
                <div className={classes.postLead}>
                  {post.node.title}
                  <div className={classes.postInfo}>
                    Read More...
                  </div>
                </div>
              </Link>
            </div>

        ))}
        <Copyright />

      </div>
    );
  }

  render() {

    const id = this.props.match.params.id;
    const { initialProps } = this.props;

    let posts = false;
    if (  (initialProps.data) &&
          (initialProps.data.posts)) {

      posts = initialProps.data.posts;
    }

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>

        { ( posts ) ?
          <div>
            { this.renderPosts(posts) }
          </div> :
          <Query query={gql(postsGql)} >

            {({ loading, error, data }) => {
              if (loading) return <Loading />;
              if (error) return <p>Error </p>;

              return this.renderPosts(data.posts);
            }}
          </Query>
        }

      </div>
    );
  }
}

export default withStyles(styles)(Blog);
