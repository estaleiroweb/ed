<?php

namespace EstaleiroWeb\ED\IO;

final class IPC_MessagesQueue extends IPC {
	protected $param = '-q';

	protected function newKey() {
		return $this->resource = msg_get_queue($this->key, self::$perms);
	}
	protected function delKey() {
		if ($this->resource) return msg_remove_queue($this->resource);
	}

	public function receive($desiredmsgtype = 0, $flags = MSG_IPC_NOWAIT) { //MSG_IPC_NOWAIT, MSG_EXCEPT, MSG_NOERROR
		if ($this->resource) return msg_receive($this->resource, $desiredmsgtype, $type, self::$size, $message, true, MSG_IPC_NOWAIT) ? $message : null;
	}
	public function send($message) {
		if ($this->resource) return msg_send($this->resource, $this->getNumType($message), $message);
	}

	public function status() {
		if ($this->resource) return msg_stat_queue($this->resource);
		/*
			msg_perm.uid   The uid of the owner of the queue.
			msg_perm.gid   The gid of the owner of the queue.
			msg_perm.mode  The file access mode of the queue.
			msg_stime      The time that the last message was sent to the queue.
			msg_rtime      The time that the last message was received from the queue.
			msg_ctime      The time that the queue was last changed.
			msg_qnum       The number of messages waiting to be read from the queue.
			msg_qbytes     The maximum number of bytes allowed in one message queue. On Linux, this value may be read and modified via /proc/sys/kernel/msgmnb.
			msg_lspid      The pid of the process that sent the last message to the queue.
			msg_lrpid      The pid of the process that received the last message from the queue.
		*/
	}
	public function set(array $data = array()) {
		if ($this->resource) return msg_set_queue($this->resource, $data);
	}
}
