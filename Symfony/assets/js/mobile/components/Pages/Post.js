import PropTypes from 'prop-types';
import React from "react";
import gql from "graphql-tag";
import { Helmet } from "react-helmet";
import { Link } from "react-router-dom";
import { Query } from "react-apollo";
import { withStyles } from '@material-ui/core/styles';
import Moment from 'react-moment';

import * as Constants from '../../constants'
import Loading from "../Trim/Loading";
import ReadMoreLess from "../Trim/ReadMoreLess";
import Copyright from "../Trim/Copyright";
import postGql from 'raw-loader!../../raw/graphql/post.graphql';
import Icon from '@material-ui/core/Icon';

import { generatePostLink, generatePostImageLink } from "../../util.js";


const postQuery = (id) => {
  return gql(postGql.replace('__POST_ID__', id));
}

const styles = theme => ({
  wrap: {
    padding: '3vw 2vw 0vw 2vw',
    backgroundColor:'#ffffff7a',
    minHeight: 'calc(100vh - 56px)',
    fontFamily: Constants.BoringFont,
  },
  image: {
    width:'100%',
    verticalAlign: 'bottom',
  },
  post: {
    paddingTop: '3vw',
    textAlign: 'center',
    background:
      'radial-gradient(ellipse at 65% 50%, rgba(36, 63, 195, 0.4) 0, rgba(255, 255, 255, 0) 100%), ' +
      'linear-gradient(0deg, rgba(48,180,152,1) 0%, rgba(25,194,119,1) 100%)',
    fontSize: '8vw',
    lineHeight: '5vw',
    padding: '0 2vw',
    '& strong': {
      width: '90vw',
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
  text: {
    '& p': {
      padding: '2vw',
      margin: 0,
      fontSize: '5vw',
      lineHeight: '7vw',
      opacity: '.9',
      color: 'white',
      textShadow: '2px 2px 2px #187b64',
    },
    textAlign: 'left',
  },
  info: {
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
    '& b': {
      float: 'right',
      lineHeight: '7vw',
    }
  },
  comments: {
    background:
      'radial-gradient(ellipse at 65% 50%, rgba(36, 63, 195, 0.4) 0, rgba(255, 255, 255, 0) 100%), ' +
      'linear-gradient(0deg, rgba(48,180,152,1) 0%, rgba(25,194,119,1) 100%)',
    padding: '4vw 2vw 6vw 2vw',
  },
  commentText: {
    border: '.8vw solid #ffffff9e',
    borderRadius: '2vw',
    marginLeft: '20vw',
    position: 'relative',
    backgroundColor: '#ffffff5c',
    fontSize: '5vw',
    lineHeight: '6.5vw',
    padding: '2vw',

    '&:before': {
      content: '""',
      position: 'absolute',
      top: '3vw',
      right: '100%',
      width: '0',
      height: '0',
      borderTop: '3vw solid transparent',
      borderBottom: '3vw solid transparent',
      borderRight: '4vw solid #ffffff99',
    },
  },
  comment: {
    marginBottom: '4vw',
    marginTop: '4vw',
    '& .content': {
      overflow: 'hidden',
      transition: 'max-height .5s',
      textOverflow: 'ellipsis',
    },
    '& .content.overflowed': {
      marginBottom: '7vw',
    },
    '& .content.more': {
        maxHeight: '200vw',
    },
    '& .content.less': {
        maxHeight: '20vw',
    },
    '& .content.overflowed.less .showMore': {
      display: 'block',
    },
    '& .content.overflowed.more .showLess': {
      display: 'block',
    },
    '& .content .showLink': {
      display: 'none',
      textShadow: '1px 1px 3px #187b64',
      color: 'white',
      fontWeight: 'bold',
      position: 'absolute',
      bottom: '1vw',
      right: '3vw',
    },
  },
  commentHead: {
    padding: '2vw 2vw',
    marginBottom: '2vw',
    backgroundColor: '#ffffff3c',
    '& strong': {
      textTransform: 'uppercase',
    },
    '& em': {
       opacity: '.8',
       fontSize: '90%',
    },
  },
  commentAvatar: {
    position: 'absolute',
    left: '-19vw',
    '& img': {
      width: '13vw',
      height: '13vw',
      borderRadius: '1vw',
      border: '1vw solid #ffffff9e',
    },
  },
  reply: {
    marginLeft: '3vw',
  }






});

class Post extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
    };
  }

  renderComment = (comment, level) => {
    const { classes } = this.props;

    let replyMargin;
    let indent = 4;
    let maxDepth = 3;

    if (level > maxDepth) {
      replyMargin = (maxDepth * indent) + 'vw';
    }
    else{
      replyMargin = (level * indent) + 'vw';
    }

    return (
      <div className={classes.comment} key={comment.node.id}>
        <div className={classes.commentText}>
          <div className={classes.commentAvatar}>
            <img src={comment.node.user.avatar_url}
              title={comment.node.user.first_name + ' ' +
              comment.node.user.last_name} />
          </div>
          <div className={classes.commentHead}>
            <strong>
              {comment.node.user.first_name} {comment.node.user.last_name}<br/>
            </strong>
            <em>
              posted <Moment interval={30000} fromNow ago>
                {comment.node.created_at}
              </Moment> ago
            </em>
          </div>
          <ReadMoreLess content={comment.node.text} />
        </div>

        { ( comment.node.replies ) &&
          ( comment.node.replies.edges.length ) ?
          comment.node.replies.edges.map((comment, index) => {
            return (
              <div style={{marginLeft: replyMargin}} key={'reply-' + comment.id}>
              { this.renderComment(comment, level+1) }
              </div>
            )
          }) : null
        }
      </div>
    );
  }

  renderComments = (comments) => {
    const { classes } = this.props;

    return (
      <div className={classes.comments}>

        {comments.edges.map((comment, index) => {
          return this.renderComment(comment, 1);
        })}
      </div>
    );
  }

  renderPost(post) {
    const { classes } = this.props;
    let text = post.lead.replace(/[\r|\n]/g, '').trim() +
      post.fold.replace(/[\n|\r]/g, '').trim();

    return (
      <div className={classes.wrap}>

        { (post.image) ?
          <img
            className={classes.image}
            src={generatePostImageLink(post)} /> : null
        }

        <div className={classes.post}>
          <strong>{post.title}</strong>
          <div
            className={classes.text}
            dangerouslySetInnerHTML={{__html: text}} />
          <div className={classes.info}>
            <Icon style={{verticalAlign: 'middle'}}>perm_identity</Icon>{post.user.first_name}&nbsp;
            <em>
              <Moment interval={30000} fromNow ago>
                {post.published_at}
              </Moment> Ago
            </em>

            <b>{post.comment_count} comments</b>
          </div>
        </div>

        {this.renderComments(post.comments)}

      <Copyright />

      </div>
    );
  }

  render() {

    const id = this.props.match.params.id;
    const {initialProps} = this.props;

    let post = false;
    if (  (initialProps.data) &&
          (initialProps.data.post) &&
          (id == initialProps.data.post.id)) {

      post = initialProps.data.post;
    }

    return (
      <div>
        <Helmet>
          <title>Books to Love</title>
        </Helmet>

        { ( post ) ?
          <div>
            { this.renderPost(post) }
          </div> :

          <Query
            query={postQuery(id)} >

            {({ loading, error, data }) => {
              if (loading) return <Loading />;
              if (error) return <p>Error </p>;

              return this.renderPost(data.post);
            }}

          </Query>
        }
      </div>
    );
  }
}

export default withStyles(styles)(Post);
