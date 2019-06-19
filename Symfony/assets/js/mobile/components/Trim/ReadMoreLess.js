import React from "react";

class ReadMoreLess extends React.Component {

  constructor(props, context) {
    super(props, context);

    this.state = {
      more: false,
      overflow: false,
    };
  }

  handleClick = () => {
    this.setState({more: this.state.more ? false : true });
  }

  componentDidMount() {
    // for reasons unknown, scrollHeight is
    // always 2 pixels larger than expected
    let wiggleRoom = 4;

    if ( this._content.scrollHeight > this._content.clientHeight + wiggleRoom ){
      this.setState({overflow: true});
    }
  }

  render() {
    const {content} = this.props;
    const overflowed = this.state.overflow ? 'overflowed' : '';

    return (
      <div
        className={(this.state.more ? 'content more ' : 'content less ') + overflowed}
        ref={(c) => this._content = c}
      >
        { content }

        <a className="showLink showMore" onClick={this.handleClick}>Read More...</a>
        <a className="showLink showLess" onClick={this.handleClick}>Read Less...</a>
      </div>
    )
  }
}

export default (ReadMoreLess);
